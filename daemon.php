#!/usr/bin/env php
<?php
// define( 'DAEMON_MODE', false );
define( 'DAEMON_MODE', true );
include 'config.php';

// check if media target folder is writeable
$storageDir = '/media/aggregateshares/';
if ( !is_writeable( $storageDir ) )
{
    echo "$storageDir can not be written to. Wrong user maybe ?\n";
    die();
}

$pm = new mmProcessManager();

if ( DAEMON_MODE )
{
    $out = new Output( STDOUT );

    declare( ticks=1 );

    // Trap signals that we expect to recieve
    pcntl_signal( SIGCHLD, array( $pm, 'childHandler' ) );
    pcntl_signal( SIGUSR1, array( $pm, 'childHandler' ) );
    pcntl_signal( SIGALRM, array( $pm, 'childHandler' ) );

    $pid = pcntl_fork();
    if ( $pid < 0 )
    {
        error_log( "unable to fork daemon" );
        $out->write( "ERROR: unable to fork daemon)" );
        exit( 1 );
    }
    /* If we got a good PID, then we can exit the parent process. */
    if ( $pid > 0 )
    {
    // Wait for confirmation from the child via SIGTERM or SIGCHLD, or
    // for two seconds to elapse (SIGALRM).  pause() should not return. */
    pcntl_alarm( 2 );
    sleep( 5 );

    echo "Failed spawning the daemon process\n";
    exit( 1 );
}

    // At this point we are executing as the child process
    $parentProcessID = posix_getppid();

    /* Cancel certain signals */
    pcntl_signal( SIGCHLD, SIG_DFL ); // A child process dies
    pcntl_signal( SIGTSTP, SIG_IGN ); // Various TTY signals
    pcntl_signal( SIGTTOU, SIG_IGN );
    pcntl_signal( SIGTTIN, SIG_IGN );
    pcntl_signal( SIGHUP,  SIG_IGN ); // Ignore hangup signal
    pcntl_signal( SIGTERM, SIG_DFL ); // Die on SIGTERM

    $sid = posix_setsid();
    if ( $sid < 0 )
    {
        error_log( "unable to create a new session" );
        echo "unable to create a new session\n";
        exit( 1 );
    }

    echo "Publishing daemon started. Process ID: " . getmypid() . "\n";

    // stop output completely
    fclose( STDIN );
    fclose( STDOUT );
    fclose( STDERR );

    fclose( $outFP );
    $outFP = fopen( 'log/queue.log', 'a' );
    $out = new Output( $outFP );

    // kill the parent !
    posix_kill( $parentProcessID, SIGUSR1 );
}
else
{
    $out = new Output( STDOUT );
}

// actual execution
$daemon = new mm\Daemon\Daemon();
$daemon->run();

class Output
{
    private $fp;

    function __construct( $fp )
    {
        $this->fp = $fp;
    }

    function write( $message )
    {
        if ( !is_resource( $this->fp ) )
        {
            throw new Exception( 'Not a resource' );
        }
        fputs( $this->fp, "[" . date('Y/m/d H:i:s') . "] $message\n" );
    }

    /**
     * @return Output
     */
    public static function instance()
    {
        if ( self::$instance !== null )
            return $instance;
    }

    private static $instance = null;
}
?>