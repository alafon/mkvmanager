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

    public static function fetchFiles()
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $list = array();

        try {
            try {
                //foreach( new UnsortedEpisodesFilter( $iterator ) as $file )
                foreach( glob( "{$tvShowPath}/*/*.{mkv,avi}", GLOB_BRACE ) as $file )
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
            return array( 'episode_name' => $matches[1],
                          'sub_language' => $matches[3] == "" ? null : $matches[3],
                          'sub_type' => $matches[4] );
        }
        else
            return false;
    }
}

?>