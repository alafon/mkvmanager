<?php
/**
 * File containing the mmMkvManagerSubtitles class
 */

/**
 *
 */
class mmMkvManagerSubtitles
{
    public static function fetchFilesWithoutSubtitles()
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $list = array();

        try {
            /*$directoryIterator = new RecursiveDirectoryIterator( '/home/download/downloads/complete/TV/Sorted' );

            $iterator = new RecursiveIteratorIterator( $directoryIterator );*/
            try {
                //foreach( new UnsortedEpisodesFilter( $iterator ) as $file )
                foreach( glob( "{$tvShowPath}/*/*.{mkv,avi}", GLOB_BRACE ) as $file )
                {
                    if ( filesize( $file ) < ( 25 * 1024 * 1024 ) )
                        continue;
                    $fileInfo = pathinfo( $file );
                    $basePath = "{$fileInfo['dirname']}/{$fileInfo['filename']}";
                    $subtitlesFiles = array( "$basePath.srt", "$basePath.ass" );
                    foreach( $subtitlesFiles as $subtitlesFile )
                    {
                        if ( file_exists( $subtitlesFile ) ) continue 2;
                    }
                    $list[] = basename( $file );
                }
            }
            catch( Exception $e )
            {
                echo "An exception has occured:\n";
                print_r( $e );
                return false;
            }
        }
        catch( Exception $e )
        {
            echo "An exception has occured: " . $e->getMessage() . "<br />";
        }

        return $list;
    }

    public static function fetchFiles( $showName = null )
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $list = array();

        $showNameFilter = is_null( $showName ) ? "*" : $showName;

        try {
            try {
                //foreach( new UnsortedEpisodesFilter( $iterator ) as $file )
                foreach( glob( "{$tvShowPath}/{$showNameFilter}/*.{mkv,avi}", GLOB_BRACE ) as $file )
                {
                    if ( sprintf( "%u", filesize( $file ) ) < ( 25 * 1024 * 1024 ) )
                    {
                        continue;
                    }
                    $fileInfo = pathinfo( $file );
                    $basePath = "{$fileInfo['dirname']}/{$fileInfo['filename']}";
                    $subtitlesFiles = array( "$basePath.srt", "$basePath.ass" );
                    $list[] = basename( $file );
                }
            }
            catch( Exception $e )
            {
                echo "An exception has occured:\n";
                print_r( $e );
                return false;
            }
        }
        catch( Exception $e )
        {
            echo "An exception has occured: " . $e->getMessage() . "<br />";
        }

        return $list;
    }

    public static function identify( $filename )
    {
        if ( preg_match( '/^(.*?)(\.([a-zA-Z]+))?\.([a-zA-Z]+)$/', $filename, $matches ) )
        {
            return array( 'filename' => basename( $matches[0] ),
                          'episode_path' => dirname( $matches[0] ),
                          'language' => $matches[3] == "" ? null : substr( strtolower( $matches[3] ), 0, 2 ),
                          'type' => strtolower( $matches[4] ) );
        }
        else
            return false;
    }
}

?>