<?php
if(!class_exists('Payplace_Payment_Helper_SimpleLogger')){
	class Payplace_Payment_Helper_SimpleLogger{
		private $LOGFILENAME;
		private $level;
		private $canLog;

		function Payplace_Payment_Helper_SimpleLogger($logfilename='',$loglevel='DEBUG'){
			$this->level=Payplace_Payment_Helper_SimpleLoggerLevels::fromString($loglevel);
			$this->canLog=false;
			if($logfilename==''){
				$this->LOGFILENAME= '/tmp/Payplace_transaction.log';
			}
			else{
				$this->LOGFILENAME=$logfilename;
			}


			try{
				if($this->level!=Payplace_Payment_Helper_SimpleLoggerLevels::NONE){
					if(file_exists($this->LOGFILENAME) && is_writable ( $this->LOGFILENAME)){
						$this->canLog=true;
					}
					else if(!file_exists($this->LOGFILENAME))
					{
						$fh = fopen($this->LOGFILENAME, 'a') ;
						fclose($fh);
						$this->canLog=true;
					}
					else {
						$this->LOGFILENAME='/tmp/Payplace_Payment_Helper_SimpleLogger.log';
						$fh = fopen($this->LOGFILENAME, 'a') ;
						fclose($fh);
						$this->canLog=true;
						$this->LOGFILENAME=Payplace_Payment_Helper_SimpleLoggerLevels::INFO;
					}
				}
			}catch (Exception $e){
			}
		}

		/**
		 * logToFile([$logLevel,]$message[, $logfile])
		 *
		 * Author(s): younes
		 * Date: May 11, 2013
		 *
		 * Writes the values of certain variables along with a message in a log file.
		 *
		 * Parameters:
		 *  $logLevel:  logLevel
		 *  $message:   Message to be logged
		 *
		 * Returns array:
		 *  $result[status]:   True on success, false on failure
		 *  $result[message]:  Error message
		 */

		function logToFile( $logLevel=Payplace_Payment_Helper_SimpleLoggerLevels::DEBUG, $message) {

			if(!$this->canLog)
				return;

			if($logLevel < $this->level)
				return;

			// Get time of request
			if( ($time = $_SERVER['REQUEST_TIME']) == '') {
				$time = time();
			}

			// Get IP address
			if( ($remote_addr = $_SERVER['REMOTE_ADDR']) == '') {
				$remote_addr = "REMOTE_ADDR_UNKNOWN";
			}

			// Get requested script
			if( ($request_uri = $_SERVER['REQUEST_URI']) == '') {
				$request_uri = "REQUEST_URI_UNKNOWN";
			}

			// Format the date and time
			$date = date("Y-m-d H:i:s", $time);

			try{

			// Append to the log file
			if($fd = @fopen($this->LOGFILENAME, "a")) {
				//$result = fputcsv($fd, array($date, $remote_addr, $request_uri,$, $message));
				$result = fputcsv($fd, array($date, $remote_addr ,Payplace_Payment_Helper_SimpleLoggerLevels::toString($logLevel), $message));
				fclose($fd);

				if($result > 0)
					return '';
				else
					return  'Unable to write to '.$this->LOGFILENAME.'!';
			}
			else {
				return 'Unable to open log '.$this->LOGFILENAME.'!';
			}
			}
			catch (Exception $e){

			}
		}

		function info($value = '') {
			self::logToFile(Payplace_Payment_Helper_SimpleLoggerLevels::INFO, $value);
		}


		function warning($value = '') {
			self::logToFile(Payplace_Payment_Helper_SimpleLoggerLevels::WARN, $value);
		}

		function error($value = '') {
			self::logToFile(Payplace_Payment_Helper_SimpleLoggerLevels::ERROR, $value);
		}
		function debug($value = '') {
			self::logToFile(Payplace_Payment_Helper_SimpleLoggerLevels::DEBUG, $value);
		}
	}

	class Payplace_Payment_Helper_SimpleLoggerLevels {
		const TRACE=0;
		const DEBUG=10;
		const INFO=20;
		const WARN=30;
		const ERROR=40;
		const NONE=100;


		static function toString($level){
			$str='';
			switch ($level){
				case Payplace_Payment_Helper_SimpleLoggerLevels::TRACE:
					$str='TRACE';
					break;
				case Payplace_Payment_Helper_SimpleLoggerLevels::DEBUG:
					$str='DEBUG';
					break;
				case Payplace_Payment_Helper_SimpleLoggerLevels::INFO:
					$str='INFO';
					break;
				case Payplace_Payment_Helper_SimpleLoggerLevels::WARN:
					$str='WARN';
					break;
				case Payplace_Payment_Helper_SimpleLoggerLevels::ERROR:
					$str='ERROR';
					break;
				default:
					break;
			}
			return $str;
		}

		static function fromString($level){
			$loglevel=Payplace_Payment_Helper_SimpleLoggerLevels::NONE;

			switch ($level){
				case 'TRACE':
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::TRACE;
					break;
				case 'DEBUG':
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::DEBUG;
					break;
				case 'INFO':
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::INFO;
					break;
				case 'WARN':
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::WARN;
					break;
				case 'ERROR':
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::ERROR;
					break;
				default:
					$loglevel= Payplace_Payment_Helper_SimpleLoggerLevels::NONE;
					break;
			}
			return $loglevel;
		}
	}
}
?>