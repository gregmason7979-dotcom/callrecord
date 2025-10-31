<?php
	
        class model
        {
                private $recordingBaseUrl;

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
                $this->recordingBaseUrl = defined('recording_base_url') ? rtrim(recording_base_url, '/\\') : '';
                }

                private function resolveDirectoryNameForId($rawId)
                {
                        $trimmed = trim((string) $rawId);

                        if ($trimmed === '') {
                                return '';
                        }

                        $baseDirectory = rtrim(maindirectory, '/\\');

                        if (preg_match('/^-?\d+$/', $trimmed)) {
                                $numericId = (int) $trimmed;

                                if ($numericId < 0) {
                                        return '';
                                }

                                $padded = str_pad((string) $numericId, 6, '0', STR_PAD_LEFT);
                                $candidates = array($padded);

                                if ($padded !== $trimmed) {
                                        $candidates[] = ltrim($trimmed, '0');
                                        $candidates[] = $trimmed;
                                }

                                foreach ($candidates as $candidate) {
                                        if ($candidate === '') {
                                                continue;
                                        }

                                        $fullPath = $baseDirectory . DIRECTORY_SEPARATOR . $candidate;

                                        if (is_dir($fullPath)) {
                                                return $candidate;
                                        }
                                }

                                return $padded;
                        }

                        $fullPath = $baseDirectory . DIRECTORY_SEPARATOR . $trimmed;

                        if (is_dir($fullPath)) {
                                return $trimmed;
                        }

                        return $trimmed;
                }

                public function getAgentRoster()
                {
                        $sql = "SELECT id, first_name, last_name FROM dbo.cc_user WHERE delete_date > GETDATE()";
                        $query = sqlsrv_query(connect, $sql);

                        if ($query === false) {
                                return array();
                        }

                        $roster = array();
                        $usedDomIds = array();

                        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                                if (!isset($row['id'])) {
                                        continue;
                                }

                                $rawId = (string) $row['id'];
                                $directoryName = $this->resolveDirectoryNameForId($rawId);

                                if ($rawId === '' || $directoryName === '') {
                                        continue;
                                }

                                $firstName = isset($row['first_name']) ? trim($row['first_name']) : '';
                                $lastName = isset($row['last_name']) ? trim($row['last_name']) : '';
                                $display = trim($firstName . ' ' . $lastName);

                                if ($display === '') {
                                        $display = $directoryName;
                                }

                                $agentDomId = preg_replace('/[^A-Za-z0-9_-]/', '', $directoryName);

                                if ($agentDomId === '') {
                                        $agentDomId = substr(md5($rawId), 0, 8);
                                }

                                $baseDomId = $agentDomId;
                                $suffix = 2;

                                while (isset($usedDomIds[$agentDomId])) {
                                        $agentDomId = $baseDomId . '-' . $suffix;
                                        $suffix++;
                                }

                                $usedDomIds[$agentDomId] = true;

                                $roster[] = array(
                                        'domId' => $agentDomId,
                                        'directory' => $directoryName,
                                        'displayName' => $display,
                                        'agentId' => $rawId,
                                );
                        }

                        sqlsrv_free_stmt($query);

                        return $roster;
                }

                public function getServiceGroups()
                {
                        $sql = "SELECT id, name FROM dbo.service_grp WHERE id > 0 ORDER BY name";
                        $query = sqlsrv_query(connect, $sql);

                        if ($query === false) {
                                return array();
                        }

                        $groups = array();
                        $seenNames = array();

                        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                                if (!isset($row['name'])) {
                                        continue;
                                }

                                $name = trim((string) $row['name']);

                                if ($name === '') {
                                        continue;
                                }

                                $normalized = strtolower($name);

                                if (isset($seenNames[$normalized])) {
                                        continue;
                                }

                                $seenNames[$normalized] = true;

                                $groups[] = array(
                                        'name' => $name,
                                        'id' => isset($row['id']) ? (int) $row['id'] : null,
                                );
                        }

                        sqlsrv_free_stmt($query);

                        return $groups;
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
                private function prepareRecordingSegments(array $segments)
                {
                        $clean = array();

                        foreach ($segments as $segment) {
                                if (!is_string($segment)) {
                                        continue;
                                }

                                $sanitized = trim(str_replace(array('/', '\\'), '', $segment));

                                if ($sanitized === '' || $sanitized === '.' || $sanitized === '..') {
                                        continue;
                                }

                                $clean[] = $sanitized;
                        }

                        return $clean;
                }

                private function encodeSegmentForUrl($segment)
                {
                        $encoded = rawurlencode($segment);

                        return strtr($encoded, array('%24' => '$'));
                }

                private function buildPublicRecordingUrl(array $segments)
                {
                        $cleanSegments = $this->prepareRecordingSegments($segments);

                        if (empty($cleanSegments) || empty($this->recordingBaseUrl)) {
                                return '';
                        }

                        $encodedSegments = array_map(array($this, 'encodeSegmentForUrl'), $cleanSegments);

                        return $this->recordingBaseUrl . '/' . implode('/', $encodedSegments);
                }

                private function buildDownloadHref(array $segments, $downloadName)
                {
                        $cleanSegments = $this->prepareRecordingSegments($segments);

                        if (empty($cleanSegments)) {
                                return '#';
                        }

                        $safeName = basename($downloadName);
                        $publicUrl = $this->buildPublicRecordingUrl($segments);

                        if ($publicUrl !== '') {
                                $query = http_build_query(array(
                                        'download' => $publicUrl,
                                        'filename' => $safeName,
                                ));

                                return 'index.php?' . $query;
                        }

                        $relativePath = implode('/', $cleanSegments);

                        $query = http_build_query(array(
                                'download' => $relativePath,
                                'filename' => $safeName,
                        ));

                        return 'index.php?' . $query;
                }

                private function extractTimestampFromFilename($filename)
                {
                        $parts = explode('$', $filename);

                        if (count($parts) < 2) {
                                return null;
                        }

                        $candidate = $parts[1];

                        if (!preg_match('/^\d{14}$/', $candidate)) {
                                return null;
                        }

                        $date = \DateTime::createFromFormat('YmdHis', $candidate);

                        if ($date === false) {
                                return null;
                        }

                        return $date->getTimestamp();
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
                                                        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
                                                        $last_modified = $this->extractTimestampFromFilename($file);

                                                        if ($last_modified === null) {
                                                                $last_modified = @filemtime($fullPath);
                                                        }

                                                        $time_passed_array = array();

                                                        if (is_int($last_modified) && $last_modified > 0) {
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
                                                        }

                    $array[] = array('file'         => $file,
                                                             'timestamp'    => is_int($last_modified) ? $last_modified : null,
                                                             'date'         => is_int($last_modified) ? date ($date_format, $last_modified) : '',
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

                public function renderRecordingRow($index, array $pathSegments, $downloadName, $otherparty, $datetime, $servicegroup, $callId, $description)
                {
                        $playUrl = $this->buildPublicRecordingUrl($pathSegments);
                        $downloadLabel = $downloadName !== '' ? $downloadName : 'recording.mp3';
                        $downloadHref = $this->buildDownloadHref($pathSegments, $downloadLabel);
                        $otherpartyEsc = htmlspecialchars($otherparty, ENT_QUOTES, 'UTF-8');
                        $serviceEsc = htmlspecialchars($servicegroup, ENT_QUOTES, 'UTF-8');
                        $descriptionEsc = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
                        $callEsc = htmlspecialchars($callId, ENT_QUOTES, 'UTF-8');
                        $dateDisplay = date('d/m/Y H:i:s A', strtotime($datetime));
                        $dateEsc = htmlspecialchars($dateDisplay, ENT_QUOTES, 'UTF-8');
                        $downloadHref = htmlspecialchars($downloadHref, ENT_QUOTES, 'UTF-8');
                        $downloadAttr = htmlspecialchars($downloadLabel, ENT_QUOTES, 'UTF-8');
                        $playArgument = json_encode($playUrl, JSON_UNESCAPED_SLASHES);
                        if ($playArgument === false) {
                                $playArgument = json_encode('');
                        }
                        $onclick = htmlspecialchars('DHTMLSound('.$playArgument.','.$index.')', ENT_QUOTES, 'UTF-8');

                        return <<<HTML
                                  <tr class="table_row table_row--detail">
                                   <td class="record-cell record-cell--actions">
                                     <div class="action-toolbar">
                                       <a href="javascript:void(0)" class="action-icon action-icon--play" onclick="{$onclick}">
                                         <span class="sr-only">Play recording</span>
                                         <svg viewBox="0 0 24 24" role="presentation"><path fill="currentColor" d="M9.5 7.4a.75.75 0 0 1 1.15-.64l6.25 4.1a.75.75 0 0 1 0 1.28l-6.25 4.1A.75.75 0 0 1 9.5 15.6Z"/></svg>
                                       </a>

                                       <a class="download-link" href="{$downloadHref}" download="{$downloadAttr}">
                                         <span class="download-link__icon" aria-hidden="true"><svg viewBox="0 0 24 24" role="presentation"><path fill="currentColor" d="M12 3.25a.75.75 0 0 1 .75.75v8.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V4a.75.75 0 0 1 .75-.75Zm-5.25 13a.75.75 0 0 0 0 1.5h10.5a.75.75 0 0 0 0-1.5Z"/></svg></span>
                                         <span>Download</span>
                                       </a>
                                     </div>
                                     <div id="dummyspan_{$index}" class="dummyspan" aria-live="polite"></div>
                                   </td>
                                   <td class="record-cell record-cell--other">{$otherpartyEsc}</td>
                                   <td class="record-cell record-cell--datetime">{$dateEsc}</td>
                                   <td class="record-cell record-cell--group"><span class="record-pill record-pill--group">{$serviceEsc}</span></td>
                                   <td class="record-cell record-cell--call"><span class="record-pill record-pill--id">{$callEsc}</span></td>
                                   <td class="record-cell record-cell--description">{$descriptionEsc}</td>
                                  </tr>
HTML;
                }


                function get_directories($user,$value_full)
                {
                        $scope = (isset($_POST['scope']) && $_POST['scope'] === 'all') ? 'all' : 'recent';
                        $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
                        if ($page < 1) {
                                $page = 1;
                        }

                        $perPage = 20;
                        $recentCutoff = strtotime('-14 days');

                        $baseDirectory = rtrim(maindirectory, '/\\');
                        $recordings = array();
                        $subdirectory = $baseDirectory . DIRECTORY_SEPARATOR . $value_full;

                        if (is_dir($subdirectory)) {
                                $list = $this->Sort_Directory_Files_By_Last_Modified($subdirectory);

                                foreach ($list[0] as $value) {
                                        if (in_array($value['file'], array(".",".."))) {
                                                continue;
                                        }

                                        $agentPath = $subdirectory . DIRECTORY_SEPARATOR . $value['file'];
                                        $timestamp = null;

                                        if (isset($value['timestamp']) && is_int($value['timestamp'])) {
                                                $timestamp = $value['timestamp'];
                                        } else {
                                                $timestamp = $this->extractTimestampFromFilename($value['file']);

                                                if ($timestamp === null) {
                                                        $timestamp = @filemtime($agentPath);
                                                        if ($timestamp !== false) {
                                                                $timestamp = (int) $timestamp;
                                                        } else {
                                                                $timestamp = null;
                                                        }
                                                }
                                        }

                                        if (is_dir($agentPath)) {
                                                $ulist = $this->Sort_Directory_Files_By_Last_Modified($agentPath);

                                                foreach ($ulist[0] as $uval) {
                                                        $recordPath = $agentPath . DIRECTORY_SEPARATOR . $uval['file'];

                                                        if (!is_file($recordPath)) {
                                                                continue;
                                                        }

                                                        $fileTimestamp = null;

                                                        if (isset($uval['timestamp']) && is_int($uval['timestamp'])) {
                                                                $fileTimestamp = $uval['timestamp'];
                                                        } else {
                                                                $fileTimestamp = $this->extractTimestampFromFilename($uval['file']);

                                                                if ($fileTimestamp === null) {
                                                                        $fileTimestamp = @filemtime($recordPath);

                                                                        if ($fileTimestamp !== false) {
                                                                                $fileTimestamp = (int) $fileTimestamp;
                                                                        } else {
                                                                                $fileTimestamp = null;
                                                                        }
                                                                }
                                                        }

                                                        if ($scope === 'recent' && $fileTimestamp !== null && $fileTimestamp < $recentCutoff) {
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

                                                        $recordings[] = array(
                                                                'segments' => array($value_full, $value['file'], $uval['file']),
                                                                'downloadName' => $uval['file'],
                                                                'otherparty' => $uotherparty,
                                                                'datetime' => $udatetime,
                                                                'servicegroup' => $uservicegroup,
                                                                'callId' => $ucallId,
                                                                'description' => $udescription,
                                                                'timestamp' => $fileTimestamp,
                                                        );
                                                }
                                        }

                                        $recordPath = $agentPath;

                                        if (!is_file($recordPath)) {
                                                continue;
                                        }

                                        if ($scope === 'recent' && $timestamp !== null && $timestamp < $recentCutoff) {
                                                continue;
                                        }

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

                                        $recordings[] = array(
                                                'segments' => array($value_full, $value['file']),
                                                'downloadName' => $value['file'],
                                                'otherparty' => $otherparty,
                                                'datetime' => $datetime,
                                                'servicegroup' => $servicegroup,
                                                'callId' => $callId,
                                                'description' => $description,
                                                'timestamp' => $timestamp,
                                        );
                                }
                        }

                        $totalRecords = count($recordings);

                        $agentAttr = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
                        $directoryAttr = htmlspecialchars($value_full, ENT_QUOTES, 'UTF-8');
                        $scopeAttr = htmlspecialchars($scope, ENT_QUOTES, 'UTF-8');

                        $print = '<div class="recording-panel" data-agent="' . $agentAttr . '" data-directory="' . $directoryAttr . '" data-scope="' . $scopeAttr . '">';
                        $print .= '<div class="recording-panel__controls">';

                        if ($scope === 'recent') {
                                $print .= '<h3 class="recording-panel__title">Recent recordings (last 14 days)</h3>';
                                $print .= '<button type="button" class="recording-panel__toggle" data-role="show-all" data-scope="all" data-agent="' . $agentAttr . '" data-directory="' . $directoryAttr . '">View all recordings</button>';
                        } else {
                                $print .= '<h3 class="recording-panel__title">All recordings</h3>';
                                $print .= '<button type="button" class="recording-panel__toggle" data-role="show-recent" data-scope="recent" data-agent="' . $agentAttr . '" data-directory="' . $directoryAttr . '">Show last 14 days</button>';
                        }

                        $print .= '</div>';

                        if ($totalRecords === 0) {
                                if ($scope === 'recent') {
                                        $print .= '<div class="recording-panel__empty">No recordings found in the last 14 days.</div>';
                                } else {
                                        $print .= '<div class="recording-panel__empty">No recordings available.</div>';
                                }

                                $print .= '</div>';
                                echo $print;
                                return;
                        }

                        $totalPages = (int) ceil($totalRecords / $perPage);

                        if ($totalPages < 1) {
                                $totalPages = 1;
                        }

                        if ($page > $totalPages) {
                                $page = $totalPages;
                        }

                        $offset = ($page - 1) * $perPage;
                        $pageRecords = array_slice($recordings, $offset, $perPage);

                        $rangeStart = $offset + 1;
                        $rangeEnd = $offset + count($pageRecords);

                        $print .= '<p class="recording-panel__meta">Showing ' . $rangeStart . '&ndash;' . $rangeEnd . ' of ' . $totalRecords . ' recordings</p>';
                        $print .= '<table class="record-table record-table--detail">';

                        $print .= '<colgroup><col class="record-col record-col--actions"><col class="record-col record-col--other"><col class="record-col record-col--datetime"><col class="record-col record-col--group"><col class="record-col record-col--call"><col class="record-col record-col--description"></colgroup>';

                        foreach ($pageRecords as $index => $record) {
                                $rowIndex = $index + 1;
                                $print .= $this->renderRecordingRow(
                                        $rowIndex,
                                        $record['segments'],
                                        $record['downloadName'],
                                        $record['otherparty'],
                                        $record['datetime'],
                                        $record['servicegroup'],
                                        $record['callId'],
                                        $record['description']
                                );
                        }

                        $print .= '</table>';

                        if ($totalPages > 1) {
                                $print .= '<nav class="pagination" aria-label="Recordings pagination">';

                                if ($page > 1) {
                                        $prev = $page - 1;
                                        $print .= '<button type="button" class="pagination__btn" data-role="page" data-page="' . $prev . '" data-scope="' . $scopeAttr . '" data-agent="' . $agentAttr . '" data-directory="' . $directoryAttr . '">Previous</button>';
                                } else {
                                        $print .= '<span class="pagination__placeholder"></span>';
                                }

                                $print .= '<span class="pagination__status">Page ' . $page . ' of ' . $totalPages . '</span>';

                                if ($page < $totalPages) {
                                        $next = $page + 1;
                                        $print .= '<button type="button" class="pagination__btn" data-role="page" data-page="' . $next . '" data-scope="' . $scopeAttr . '" data-agent="' . $agentAttr . '" data-directory="' . $directoryAttr . '">Next</button>';
                                } else {
                                        $print .= '<span class="pagination__placeholder"></span>';
                                }

                                $print .= '</nav>';
                        }

                        $print .= '</div>';

                        echo $print;


                }

		
	}
?>
