<?php
Class pm_log 
{
	const USER_ERROR_DIR = '/logs/Site_User_errors.log';
	const GENERAL_ERROR_DIR = '/logs/Site_General_errors.log';
	const MSG_DIR = 'Elastic_Plugin_Messages.txt';

	/*
	 User Errors...
	*/
	public function user( $msg,$username ) {
		$date = date('d.m.Y h:i:s');
		$log = $msg."   |  Date:  ".$date."  |  User:  ".$username."\n";
		error_log( $log, 3, self::USER_ERROR_DIR);
	}
	/*
	 General Errors...
	*/
	public function general( $msg ) {
		$date = date('d.m.Y h:i:s');
		$log = $msg."   |  Date:  ".$date."\n";
		error_log( $msg."   |  Pd_Elastic_Error:  ".$date . "\n" . '----------------------' . "\n", 3, self::GENERAL_ERROR_DIR);
	}
	
	/*
	 Message logging
	*/
	public function message( $msg) {
		$date = date('d.m.Y h:i:s');
		$log = $msg."   |  Date:  ".$date."\n";
		error_log((string) $msg . "   |  Message logged:  ".$date . "\n" . '----------------------' . "\n", 3, self::MSG_DIR );
	}

}
