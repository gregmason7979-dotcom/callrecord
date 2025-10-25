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
		function get_directories($user,$value_full)
		{
				$i=0;
			$directory = maindirectory;
			$print ='';
			$print = '<table class="show">';
		
				$subdirectory	=	$directory.$value_full;
				if(is_dir($subdirectory))
				{
				
					 $list = $this->Sort_Directory_Files_By_Last_Modified($subdirectory);
					/*  echo '<pre>';print_R($list); echo'</pre>';  */
					 foreach($list[0] as $value)
					{
						
						if (!in_array($value['file'],array(".",".."))) 
					 {
					 if(is_dir($directory.$value_full.'/'.$value['file']))
					 {
					  $ulist = $this->Sort_Directory_Files_By_Last_Modified($directory.$value_full.'/'.$value['file']);
					     foreach($ulist[0] as $uval)
					{
					$i++;
					$uplay	=	$directory.$value_full.'/'.$value['file'].'/'.$uval['file'];
						if(is_file($uplay)){
						$uexplode	=	explode('$',$uval['file']);
						$uservicegroup	=	$uexplode[0];
						$udatetime		=	$uexplode[1];
						$udescription	=	$uexplode[3];
						$uotherparty	=	$uexplode[2]; 
						$ucallid		=	$uexplode[4];
						$ucall			=	explode('.',$ucallid);
						$click="'http://192.168.1.154/SeCRecord/".$value_full."/".$value['file']."/".$this->replacePlus($uval['file'])."'";
						$print .=  '<tr>
					  
					   <td width="350">
					   <span id="dummyspan_'.$i.'" class="dummyspan"></span>
						<a href="javascript:void(0)" onclick="DHTMLSound('.$click.','.$i.')"><img src="images/play_btn.png" /></a>
					   
					   <a href="index.php?download='.urlencode($uplay).'&filename='.urlencode($uval['file']).'">Download</a>
					</td>
					<td width="150">'.$uotherparty.'</td>
					   <td width="250">'.date('d/m/Y H:i:s A',strtotime($udatetime)).'</td>
					   <td>'.$uservicegroup.'</td>
					   <td>'.$ucall[0].'</td>
					   <td>'.$udescription.'</td>
					   </tr>';
					  
					}
					 }
					 }
						$i++;
						$play	=	$directory.$value_full.'/'.$value['file'];
						if(is_file($play)){
						$explode	=	explode('$',$value['file']);
						$servicegroup	=	$explode[0];
						$datetime		=	$explode[1];
						$description	=	$explode[3];
						$otherparty		=	$explode[2]; 
						$callid			=	$explode[4];
						$call			=	explode('.',$callid);
						$clicknew="'http://192.168.1.154/SeCRecord/".$value_full."/".$this->replacePlus($value['file'])."'";
				$print .='
				
					  <tr>
					  
					   <td width="350">
					   <span id="dummyspan_'.$i.'" class="dummyspan"></span>
						<a href="javascript:void(0)" onclick="DHTMLSound('.$clicknew.','.$i.')"><img src="images/play_btn.png" /></a>
					   
					   <a href="index.php?download='.urlencode($play).'&filename='.urlencode($value['file']).'">Download</a>
					</td>
					<td width="150">'.$otherparty.'</td>
					   <td width="250">'. date('d/m/Y H:i:s A',strtotime($datetime)).'</td>
					   <td>'.$servicegroup.'</td>
					   <td>'.$call[0].'</td>
					   <td>'.$description.'</td>
					   </tr>';
					 
					 
					  }
					  } 
					 } 
				}
				
				 $print .= '</table>';
				echo $print;
		
		
		}
		
	}
?>