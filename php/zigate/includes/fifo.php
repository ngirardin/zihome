<?php
ob_implicit_flush();

class fifo {
	var $fp;
	var $to;

	function fifo( $file, $mode ) 
	{
		if( !file_exists( $file ) ) 
		{
			//print "creating pipe on fs\n";
			if( !posix_mkfifo( $file, 0777 ) ) 
			{
				die( "could not create named pipe $file\n" );
			}
			chown($file, "www-data");
		}
		//print "opening fifo $file for $mode\n";
		$this->fp = fopen( $file, $mode );
	}

	function read() {
	// reads a line from a fifo file
		$line = '';
		do 
		{
			$c = fgetc( $this->fp );
			if( ($c != '') and ($c != "\n") and !feof( $this->fp ) ) $line .= $c;
		} while( ($c != '') and ($c != "\n") and !feof( $this->fp ) );
		return $line;
	}

	function write( $data ) {
		fputs( $this->fp, $data );
		fflush( $this->fp );
	}
	
	function close()
	{
		fclose($this->fp);
	}

}



?>