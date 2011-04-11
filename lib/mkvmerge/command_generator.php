<?php
/**
 * Generates a MKVMergeCommand from a TV Show file
 */
class MKVMergeTVCommandGenerator
{
    /**
     * Generates an MKVMerge command from a TVShow episode filename $filename
     * @param string $filename
     * @return MKVMergeCommand
     */
    public static function generate( $filename )
    {
        $commandTemplate =
            'mkvmerge -o "%s" ' . // OutputFile
            '"--sub-charset" "0:ISO-8859-1" "--language" "0:fre" "--forced-track" "0:no" "-s" "0" "-D" "-A" "-T" ' .
            '"--no-global-tags" "--no-chapters" "%s" ' . // SourceSubtitleFile
            '"--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:16x9" ' .
            '"--language" "2:eng" "--default-track" "2:yes" "--forced-track" "2:no" "-a" "2" "-d" "1" "-S" "-T" ' .
            '"--no-global-tags" "--no-chapters" "%s" ' . // SourceVideoFile
            '"--track-order" "0:0,1:1,1:2"';

        // input is a .mkv or .avi file
        preg_match( '/^((.*) - [0-9]+x[0-9]+ - (.*))\.(avi|mkv)$/', $filename, $matches );
        $episodeName = $matches[1];
        $showName = $matches[2];
        $directory = "/home/download/downloads/complete/TV/Sorted/{$showName}";
        $videoSourceFile = "{$directory}/{$filename}";

        // 3. replace source video / sub and output file in command
        //
        // Options:
        // - Check subtitle charset
        // - Handle multiple video files (.en.srt + .fr.srt)

        // 1. look for subtitle file, .ass or .srt
        foreach( array( "{$directory}/{$episodeName}.srt", "{$directory}/{$episodeName}.ass" ) as $subtitleFile )
        {
            if ( !file_exists( $subtitleFile ) )
                unset( $subtitleFile );
            else
                break;
        }
        if ( !isset( $subtitleFile ) )
            throw new Exception( "No subtitles found" );

        // 2. extract relevant output path
        //    *** might require a target path choice, like mkvmerge2
        $outputFile = "/media/storage/foobar/TV Shows/{$showName}/{$episodeName}.mkv";
        $command = sprintf( $commandTemplate, $outputFile, $subtitleFile, $videoSourceFile );
        return new MKVMergeCommand( $command );
    }

    /**
     * Constructs a new command generator object
     */
    public function construct()
    {
        $this->tracks = new MKVmergeCommandTrackSet();
    }

    /**
     * Adds the input file $file to the command
     * @param MKVMergeSourceFile $file
     */
    public function addInputFile( MKVMergeInputFile $file )
    {
        foreach( $file->getTracks() as $track )
        {
            $this->addTrack( $track );
        }
    }

    /**
     * Sets the target disk for the output file to $disk
     *
     * This method is a helper for setOutputFile
     *
     * @param string $disk
     */
    public function setTargetDisk( $disk )
    {

    }

    /**
     * Sets the output file for the command to $outputFile
     *
     * @param string $outputFile
     */
    public function setOutputFile( $outputFile )
    {

    }

    private function addTrack( MKVMergeCommandTrack $track )
    {
        $this->tracks[] = $track;
    }

    /**
     * The tracks the command manages
     * @var MKVMergeCommandGeneratorTrackSet
     */
    private $tracks;
}

/**
 * MKVMergeSourceFile
 *
 */
abstract class MKVMergeInputFile
{
    /**
     * Returns the input file's tracks
     * @return MKVmergeCommandTrackSet
     */
    public function getTracks()
    {
        return $tracks;
    }

    /**
     * Adds a new track to the input file
     */
    protected function addTrack( MKVMergeCommandTrack $track )
    {
        $this->tracks[] = $track;
    }

    /**
     * @var MKVMergeCommandTrackSet
     */
    private $tracks;
}

/**
 * MKVMergeSubtitleSourceFile
 * One subtitle file, with its language
 */
class MKVMergeSubtitleFile extends MKVMergeInputFile
{
    function __construct( $file, $language )
    {

    }

    private $file;
    private $language;
}

/**
 * MKVMergeSourceFile
 * Will analyze the file for tracks
 */
class MKVMergeMediaFile extends MKVMergeSourceFile
{
    function __construct( $file )
    {

    }

    private $file;
}

/**
 * Media file analyzer
 */
class MediaAnalyzer
{
    private function __construct()
    {

    }

    /**
     * Returns the appropriate analyzer for the file $file
     */
    public static function get( $file )
    {

    }
}

/**
 * A set of MKVmergeCommandTrack
 */
class MKVmergeCommandTrackSet implements ArrayAccess, Iterator, Countable
{
    /**
     * ArrayAccess::offsetExists()
     */
    public function offsetExists( $offset )
    {
        return isset( $this->tracks[$offset] );
    }

    /**
     * ArrayAccess::offsetGet()
     * @return MKVMergeCommandTrack
     */
    public function offsetGet( $offset )
    {
        return isset( $this->tracks[$offset] ) ? $this->tracks[$offset] : null;
    }

    /**
     * ArrayAccess::offsetSet()
     */
    public function offsetSet( $offset, MKVManagerCommandTrack $value )
    {
        if ( $offset === '' )
            $this->tracks[] = $value;
        else
            $this->tracks[$offset] = $value;
    }

    /**
     * ArrayAcces::offsetUnset()
     */
    public function offsetUnset( $offset )
    {
        unset( $this->tracks[$offset] );
    }

    /**
     * Iterator::current()
     */
    public function current()
    {
        return $this->tracks[$key];
    }

    /**
     * Iterator::key()
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Iterator::next()
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * Iterator::rewind()
     */
    public function rewind()
    {
        $this->key = 0;
    }

    /**
     * Iterator::valid()
     */
    public function valid()
    {
        return isset( $this->tracks[$this->key] );
    }

    /**
     * Countable::count()
     */
    public function count()
    {
        return count( $this->tracks );
    }

    /**
     * @var array(MKVManagerCommandTrack)
     */
    private $tracks;

    /**
     * Iterator key
     * @var integer
     */
    private $key = 0;
}

/**
 * And MKVMerge command track
 */
class MKVmergeCommandTrack
{
    public function __construct( )
    {

    }

    private $inputFile;
}
?>