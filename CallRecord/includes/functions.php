<?php
	
	class model
	{

		function __construct()
		{
			$serverName = "GNT-SEC";
			$connectionInfo = array( "Database"=>dbname, "UID"=>username, "PWD"=>password);
			$connect	=	sqlsrv_connect( $serverName, $connectionInfo);
			
			if(!$connect)
			{
				die('Could Not Connect!');
				/* die( print_r( sqlsrv_errors(), true)); */
			}
		/* 	mssql_select_db(dbname,$connect) ; */
		define('connect',$connect);
		}
		
		function admin_login()
		{
			$username	=	trim($_POST['username']);
			$password	=	trim($_POST['password']);
			
			if($username==adminusername && $password==adminpassword)
			{
						$_SESSION['username']	=	adminusername;
						$_SESSION['login']		=	'Admin';
						header('Location:index.php');
						die;
			}else{
						$_SESSION['invalid']	=	'invalid';
						header('Location:login.php');
						die;
			}
		}
		
		function redirect($url)
		{
			header('location:'.$url.'');
			die;
		}
		function replacePlus($string){
            return str_replace('+', '%2B', $string);
        }
		function logout()
		{
			unset($_SESSION['username']);
			unset($_SESSION['login']);
			$this->redirect('login.php');
		}
		function Sort_Directory_Files_By_Last_Modified($dir, $sort_type = 'descending', $date_format = "F d Y H:i:s")
		{
			$files = scandir($dir);

			$array = array();
		
			foreach($files as $file)
			{
									if($file != '.' && $file != '..')
						{
							$now = time();
							$last_modified = filemtime($dir.'/'.$file);
						  
							$time_passed_array = array();

							$diff = $now - $last_modified;

							$days = floor($diff / (3600 * 24));

							if($days)
							{
							$time_passed_array['days'] = $days;
							}

							$diff = $diff - ($days * 3600 * 24);

							$hours = floor($diff / 3600);

							if($hours)
							{
							$time_passed_array['hours'] = $hours;
							}

							$diff = $diff - (3600 * $hours);

							$minutes = floor($diff / 60);

							if($minutes)
							{
							$time_passed_array['minutes'] = $minutes;
							}

							$seconds = $diff - ($minutes * 60);

							$time_passed_array['seconds'] = $seconds;

                    $array[] = array('file'         => $file,
                                                             'timestamp'    => $last_modified,
                                                             'date'         => date ($date_format, $last_modified),
                                                             'time_passed'  => $time_passed_array);
                    }
                }

                usort($array, static function ($a, $b) {
                        $aTime = $a['timestamp'] ?? null;
                        $bTime = $b['timestamp'] ?? null;

                        if ($aTime === $bTime) {
                                return 0;
                        }

                        if ($aTime === null) {
                                return 1;
                        }

                        if ($bTime === null) {
                                return -1;
                        }

                        return ($aTime < $bTime) ? -1 : 1;
                });

                if($sort_type == 'descending')
                {
                $array = array_reverse($array);
                }

                return array($array, $sort_type);
        }
                private function recordingMatchesFilters($datetime, $description, $otherparty, $servicegroup, $callId)
                {
                        if (!isset($_POST['action']) || $_POST['action'] !== 'getdirectory') {
                                return true;
                        }

                        if (!empty($_POST['name'])) {
                                return $_POST['name'] === $description;
                        }

                        if (!empty($_POST['date']) && !empty($_POST['enddate'])) {
                                $paymentDate = date('Y-m-d', strtotime($datetime));
                                return (strtotime($paymentDate) >= strtotime($_POST['date'])) && (strtotime($paymentDate) <= strtotime($_POST['enddate']));
                        }

                        if (!empty($_POST['other_party'])) {
                                return $_POST['other_party'] === $otherparty;
                        }

                        if (!empty($_POST['service_group'])) {
                                return $_POST['service_group'] === $servicegroup;
                        }

                        if (!empty($_POST['call_id'])) {
                                return $_POST['call_id'] === $callId;
                        }

                        return true;
                }

                private function appendRecordingRow($print, $index, $clickTarget, $downloadPath, $downloadName, $otherparty, $datetime, $servicegroup, $callId, $description)
                {
                        $print .= $this->renderRecordingRow($index, $clickTarget, $downloadPath, $downloadName, $otherparty, $datetime, $servicegroup, $callId, $description);

                        return $print;
                }

                public function renderRecordingRow($index, $clickTarget, $downloadPath, $downloadName, $otherparty, $datetime, $servicegroup, $callId, $description)
                {
                        $otherpartyEsc = htmlspecialchars($otherparty, ENT_QUOTES, 'UTF-8');
                        $serviceEsc = htmlspecialchars($servicegroup, ENT_QUOTES, 'UTF-8');
                        $descriptionEsc = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
                        $callEsc = htmlspecialchars($callId, ENT_QUOTES, 'UTF-8');
                        $dateDisplay = date('d/m/Y H:i:s A', strtotime($datetime));
                        $dateEsc = htmlspecialchars($dateDisplay, ENT_QUOTES, 'UTF-8');
                        $downloadUrl = 'index.php?download='.urlencode($downloadPath).'&filename='.urlencode($downloadName);
                        $downloadHref = htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8');
                        $onclick = htmlspecialchars('DHTMLSound('.$clickTarget.','.$index.')', ENT_QUOTES, 'UTF-8');

                        return <<<HTML
                                  <tr class="table_row">

                                   <td width="350" class="record-actions">
                                     <div class="action-toolbar">
                                       <a href="javascript:void(0)" class="action-icon action-icon--play" onclick="{$onclick}">
                                         <span class="sr-only">Play recording</span>
                                         <svg viewBox="0 0 24 24" role="presentation"><path fill="currentColor" d="M9.5 7.4a.75.75 0 0 1 1.15-.64l6.25 4.1a.75.75 0 0 1 0 1.28l-6.25 4.1A.75.75 0 0 1 9.5 15.6Z"/></svg>
                                       </a>

                                       <a class="download-link" href="{$downloadHref}">
                                         <span class="download-link__icon" aria-hidden="true"><svg viewBox="0 0 24 24" role="presentation"><path fill="currentColor" d="M12 3.25a.75.75 0 0 1 .75.75v8.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V4a.75.75 0 0 1 .75-.75Zm-5.25 13a.75.75 0 0 0 0 1.5h10.5a.75.75 0 0 0 0-1.5Z"/></svg></span>
                                         <span>Download</span>
                                       </a>
                                     </div>
                                     <div id="dummyspan_{$index}" class="dummyspan" aria-live="polite"></div>
                                   </td>
                                   <td width="150">{$otherpartyEsc}</td>
                                   <td width="250">{$dateEsc}</td>
                                   <td><span class="record-pill record-pill--group">{$serviceEsc}</span></td>
                                   <td><span class="record-pill record-pill--id">{$callEsc}</span></td>
                                   <td>{$descriptionEsc}</td>
                                  </tr>
HTML;
                }


                function get_directories($user,$value_full)
                {
                        $i = 0;
                        $directory = maindirectory;
                        $print = '<table class="record-table record-table--detail">';
                        $subdirectory = $directory.$value_full;

                        if (is_dir($subdirectory)) {
                                $list = $this->Sort_Directory_Files_By_Last_Modified($subdirectory);

                                foreach ($list[0] as $value) {
                                        if (in_array($value['file'], array(".",".."))) {
                                                continue;
                                        }

                                        $agentPath = $directory.$value_full.'/'.$value['file'];

                                        if (is_dir($agentPath)) {
                                                $ulist = $this->Sort_Directory_Files_By_Last_Modified($agentPath);

                                                foreach ($ulist[0] as $uval) {
                                                        $recordPath = $agentPath.'/'.$uval['file'];

                                                        if (!is_file($recordPath)) {
                                                                continue;
                                                        }

                                                        $uexplode = explode('$', $uval['file']);
                                                        if (count($uexplode) < 5) {
                                                                continue;
                                                        }

                                                        $uservicegroup = $uexplode[0];
                                                        $udatetime = $uexplode[1];
                                                        $uotherparty = $uexplode[2];
                                                        $udescription = $uexplode[3];
                                                        $ucall = explode('.', $uexplode[4]);
                                                        $ucallId = $ucall[0];

                                                        if (!$this->recordingMatchesFilters($udatetime, $udescription, $uotherparty, $uservicegroup, $ucallId)) {
                                                                continue;
                                                        }

                                                        $click = "'http://192.168.1.154/SeCRecord/".$value_full."/".$value['file']."/".$this->replacePlus($uval['file'])."'";
                                                        $i++;
                                                        $print = $this->appendRecordingRow($print, $i, $click, $recordPath, $uval['file'], $uotherparty, $udatetime, $uservicegroup, $ucallId, $udescription);
                                                }
                                        }

                                        $recordPath = $agentPath;

                                        if (is_file($recordPath)) {
                                                $explode = explode('$', $value['file']);
                                                if (count($explode) < 5) {
                                                        continue;
                                                }

                                                $servicegroup = $explode[0];
                                                $datetime = $explode[1];
                                                $otherparty = $explode[2];
                                                $description = $explode[3];
                                                $call = explode('.', $explode[4]);
                                                $callId = $call[0];

                                                if (!$this->recordingMatchesFilters($datetime, $description, $otherparty, $servicegroup, $callId)) {
                                                        continue;
                                                }

                                                $clicknew = "'http://192.168.1.154/SeCRecord/".$value_full."/".$this->replacePlus($value['file'])."'";
                                                $i++;
                                                $print = $this->appendRecordingRow($print, $i, $clicknew, $recordPath, $value['file'], $otherparty, $datetime, $servicegroup, $callId, $description);
                                        }
                                }
                        }

                        $print .= '</table>';
                        echo $print;


                }

		
	}
?>
