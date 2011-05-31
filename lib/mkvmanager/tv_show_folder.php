<?php
/**
 * TVShow class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property string folder fullpath to the folder
 */
class TVShowFolder extends TVShow
{
    public function __construct( $name, $parentFolder )
    {
        parent::__construct( $name );
        $this->folder = "{$parentFolder}/{$name}";
    }

    public $folder;

    /**
     * Returns all the folders within the TVShow main folder
     * @todo add a validity checker (count subitems, check sickbeard, etc)
     *
     * @return array[TVShowFolder]
     */
    public static function fetchList()
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $list = array();

        foreach ( glob( "{$tvShowPath}/*" ) as $showFullPath )
        {
            $tvShowFolder = new self( basename( $showFullPath ), dirname( $showFullPath ) );
            $list[$tvShowFolder->systemName] = $tvShowFolder;
        }
        ksort($list);
        return $list;
    }

}
?>