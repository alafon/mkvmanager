<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read bool $hasSubtitleFile
 * @property-read bool $hasSubtitleFiles
 * @property-read TVEpisodeDownloadedFile $downloadedFile filename of the originally downloaded file (release)
 * @property-read string $path the file's full path
 * @property-read string $subtitleFile the file's subtitle, if it exists
 * @property-read array $subtitleFiles the file's subtitles, if it exist
 * @property-read double $fileSize the episode file's size
 * @property-read array $mkvinfo
 * @property-read bool $hasMergedSubtitles
 * @property-read array $mergedSubtitles
 */
class TVEpisodeFile
{
    /**
     * Constructs a TVEpisodeFile based on the filename $filename
     * @param string $filename
     */
    public function __construct( $filename, $loadExtras = false )
    {
        $pathinfo = pathinfo( $filename );
        $this->filename = $filename;
        $this->fullname = $pathinfo['filename'];
        $this->extension = $pathinfo['extension'];
        if ( preg_match( '/^(.*?) - [sS]?([0-9]+)[xXeE]([0-9]+)(-[0-9]+)? - (.*)$/', $this->fullname, $matches ) )
            list(, $this->showName, $this->seasonNumber, $this->episodeNumber, $this->episodeNumber2, $this->episodeName ) = $matches;
        $this->checkValidity();

        if ( $loadExtras === true ) {
            $cachedPropertieKeys = array( 'mkvinfo', 'hasMergedSubtitles', 'mergedSubtitles', 'hasSubtitleFiles', 'subtitleFiles' );
            foreach ( $cachedPropertieKeys as $cachedPropertyKey )
            {
                $this->cache[$cachedPropertyKey] = $this->$cachedPropertyKey;
            }
        }
    }

    /**
     * Loop against properties provided by requiredProperties()
     * Set isValid to false if at least one was null
     *
     * @return void
     */
    private function checkValidity()
    {
        $valid = true;
        foreach( self::requiredProperties() as $requiredProperty )
        {
            if( is_null( $this->$requiredProperty ) )
            {
               $valid = false;
               break;
            }

        }
        $this->isValid = $valid;
    }

    /**
     * Return an array of the required properties needed to be a valid TVEpisodeFile object
     * Used by checkValidity()
     *
     * @return array
     */
    static public function requiredProperties()
    {
        return array( 'showName', 'seasonNumber', 'episodeNumber', 'episodeName', 'fullname' );
    }

    public function __get( $property )
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $basedir = "{$tvShowPath}/{$this->showName}/";
        switch( $property )
        {
            case 'mkvinfo':
                $basedirAndFilename = $basedir . $this->filename;
                $systemFullPath = str_replace( ' ', '\ ', $basedirAndFilename );
                $pathinfo = pathinfo( $systemFullPath );

                //echo $systemFullPath;
                if( $pathinfo['extension'] == 'mkv' )
                {
                    $cmd = "mkvinfo " . $systemFullPath;
                    exec( $cmd, $result, $return );
                    return $return == 0 ? array( 'result' => $result, 'return' => $return ) : null;
                }
                else
                {
                    return null;
                }
                break;

            case 'hasMergedSubtitles':
                return count( $this->mergedSubtitles ) > 0;
                break;

            //@TODO change returned format
            case 'mergedSubtitles':
                $subtitles = array();
                $mkvinfo = $this->mkvinfo;
                if( !is_null($mkvinfo) )
                {
                    $xmlResult = self::parseMKVInfoResult( $mkvinfo['result'] );
                    $xmlSimple = simplexml_import_dom( $xmlResult );

                    $subtitleElements = $xmlSimple->xpath( "//A-track[@track-type='subtitles']/Language" );
                    foreach ( $subtitleElements as $subtitleElement )
                    {
                        /* @var $subtitleElement SimpleXMLElement */
                        //var_dump($subtitleElement->xpath( "//Language" ));

                        $subtitles[] = strtolower( substr( $subtitleElement->attributes()->value, 0, 2 ));
                    }
                }
                return $subtitles;
                break;

            case 'hasSubtitleFile':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                return ( file_exists( "$basedirAndFile.srt" ) || file_exists( "$basedirAndFile.ass" ) );
                break;

            case 'hasSubtitleFiles':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                $subtitles = glob( $basedirAndFile . "*.{ass,srt}", GLOB_BRACE );
                return count( $subtitles ) > 0;
                break;

            case 'subtitleFile':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                if ( file_exists( "$basedirAndFile.ass" ) )
                    return "$basedirAndFile.ass";
                elseif ( file_exists( "$basedirAndFile.srt" ) )
                {
                    return "$basedirAndFile.srt";
                }
                else
                {
                    throw new Exception("No subtitle found for $this->filename" );
                }
                break;

            case 'subtitleFiles':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                $subtitleFiles = array();
                $assQualityFiles = glob( $basedirAndFile . "*.ass" );
                $srtQualityFiles = glob( $basedirAndFile . "*.srt" );

                $identifySubtitles = function( &$value ) {
                    $value = mmMkvManagerSubtitles::identify( $value );
                };

                if( count( $assQualityFiles ) > 0 )
                {
                    array_walk( $assQualityFiles, $identifySubtitles );
                    $subtitleFiles['ass'] = $assQualityFiles;
                }
                if( count( $srtQualityFiles ) > 0 )
                {
                    array_walk( $srtQualityFiles, $identifySubtitles );
                    $subtitleFiles['srt'] = $srtQualityFiles;
                }

                if( count( $subtitleFiles ) > 0 )
                {
                    return $subtitleFiles;
                }
                else
                {
                    return false;
                }
                break;

            case 'path':
                return "{$tvShowPath}/{$this->showName}/{$this->filename}";
                break;

            case 'downloadedFile':
                $db = ezcDbInstance::get( 'sickbeard' );

                // show ID
                $q = $db->createSelectQuery();
                $q->select( 'tvdb_id' )
                  ->from( 'tv_shows' )
                  ->where( $q->expr->eq( 'show_name', $q->bindValue( $this->showName ) ) );

                /**
                 * @var PDOStatement
                 */
                $stmt = $q->prepare();
                $stmt->execute();
                $showId = $stmt->fetchColumn();

                // downloaded file name
                $q = $db->createSelectQuery();
                $e = $q->expr;
                $q->select( 'resource' )
                  ->from( 'history' )
                  ->where( $e->lAnd(
                      $e->eq( 'action', $q->bindValue( 404 ) ),
                      $e->eq( 'showid', $q->bindValue( $showId) ),
                      $e->eq( 'season', $q->bindValue( $this->seasonNumber) ),
                      $e->eq( 'episode', $q->bindValue( $this->episodeNumber ) )
                  ) );
                $stmt = $q->prepare();
                $stmt->execute();
                return new TVEpisodeDownloadedFile( basename( $downloadedFile = $stmt->fetchColumn() ) );

            case 'fileSize':
                return mmMkvManagerDiskHelper::bigFileSize( $this->path );

            case 'cache':
                return $this->cache;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }
    }

    public static function parseMKVInfoResult( $stringAsArray )
    {
        $dom = new DOMDocument();
        $root = $dom->createElement( "root" );
        $dom->appendChild( $root );

        $level = 0;
        $parent = $root;
        /* @var $lastElement DOMElement */
        /* @var $element DOMElement */
        $lastElement = $root;

        foreach ( $stringAsArray as $line )
        {
            $lastLevel = $level;

            //echo $line;
            preg_match( "/^(\|*)?(\ *)?\+\ (.+)$/i", $line, $matches );
            $level = strlen( $matches[1] ) + strlen( $matches[2] );

            preg_match( "/^([a-zA-Z\ \(\),]+)+:?(.*)/", $matches[3], $matches2 );

            // replace all specials chars by a "-"
            // trim the "-" at the beginning and the end of the string
            // (if there was some special chars at these positions)
            $nodeName = trim( preg_replace( '/[^a-z0-9]+/i', '-', $matches2[1] ), "-" );
            $nodeValue = trim( $matches2[2] );

            //echo "$level - $nodeName : $nodeValue<br />";

            $element = $dom->createElement( $nodeName );
            if ( $nodeValue != "" )
                $element->setAttribute( "value", $nodeValue );

            // add the track-type as an attribute to retrieve stuff easily
            if ( $nodeName == 'Track-type' )
            {
                $parent->setAttribute( "track-type", $nodeValue );
            }

            // parent choice depending on where we were before (based on the level)
            if ( $lastLevel == $level )
                $parent->appendChild( $element );
            elseif ( $level > $lastLevel )
            {
                $parent = $lastElement;
                $parent->appendChild( $element );
            }
            elseif ( $level < $lastLevel )
            {
                $diff = $lastLevel - $level;
                for ( $i=0; $i < $diff; $i++ )
                {
                    $parent = $parent->parentNode;
                }
                $parent->appendChild( $element );
            }
            $lastElement = $element;
        }


        return $dom;
        header( "Content-Type: text/xml" );
        echo( $dom->saveXML() );
        exit;
    }

    /**
     * TV Show name
     * @var string
     */
    public $showName;

    /**
     * Season number
     * @var integer
     */
    public $seasonNumber;

    /**
     * Episode number
     * @var integer
     */
    public $episodeNumber;

    /**
     * Episode number for double episode such as SXXEYY-ZZ
     * @var integer
     */
    public $episodeNumber2;

    /**
     * Episode name/title
     * @var string
     */
    public $episodeName;

    /**
     * File extension (mkv, avi)
     * @var string
     */
    public $extension;

    /**
     * Filename, extension included
     * @var string
     */
    public $filename;

    /**
     * Full episode name: <ShowName> - <SeasonNr>x<EpisodeNr> - <EpisodeName> without extension
     * @var string
     */
    public $fullname;

    /**
     * True if the episode has been successfully identified
     * @var boolean
     */
    public $isValid;

    public $cache;
}

?>
