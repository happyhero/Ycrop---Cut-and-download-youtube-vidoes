<?php
function calcTime($start, $end, $duration){
	if(preg_match("/^([0-1]?[0-9]|[2][0-3]):([0-5][0-9])(:[0-5][0-9])?$/", $start) && preg_match("/^([0-1]?[0-9]|[2][0-3]):([0-5][0-9])(:[0-5][0-9])?$/", $end)){
		$start_t = explode(":", $start);
		$end_t = explode(":", $end);
		if(count($start_t) < 2 || count($end_t) < 2){
			return [false, "L2"];
		}
		if(count($start_t) < 3){
			$start = '00:'.$start;
		}
		if(count($end_t) < 3){
			$end = '00:'.$end;
		}
		
		$start_tm = new DateTime($start);
		$end_tm = new DateTime($end);
		$interval = $start_tm->diff($end_tm);
		$diff = $interval->format('%H:%I:%S');
		
		$diff_ts = $end_tm->getTimestamp() - $start_tm->getTimestamp();
		
		$end_ts = strtotime($end) - strtotime('00:00:00');
		
		if(intval($diff_ts) < 2 ){
			return [false, "M2"];
		}
		else if(intval($diff_ts) > 300){
			return [false, "X2"];
		}
	//	else if($end_ts > intval($duration)){
		//	return [false, "EX"];
		//}
		return [true, $start, $diff];
	}
	return [false, "PF"];
}

function isInputGood($arr){
	$status = true;
	
	foreach($arr as $param){
		if(!isset($_POST[$param]) || empty($_POST[$param])){
			$status = false;
			break;
		}
	}
	
	return $status;
}

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
	
	if(!isInputGood(['url', 'st', 'et'])){
		echo json_encode(["Msg" => "Something went wrong."]);
	}
	else{
		
		$errors = [
			"L2" => "Invalid time.",
			"M2" => "Minimum of 2 seconds interval required.",
			"X2" => "The time interval is too big. Max is 5 mins.",
			"PF" => "Invalid time format.",
			"EX" => 'End time exceeds the actual length of the video.',
		]; 
		
		
		$start = $_POST['st'];
		$end = $_POST['et'];
		$raw_url = $_POST['url']; //the youtube video ID
		
		//$format = "mp4"; //the MIME type of the video. e.g. video/mp4, video/webm, etc.
		//parse_str(file_get_contents("http://youtube.com/get_video_info?video_id=".$id),$info); //decode the data
		
		exec("/usr/local/bin/youtube-dl --get-url -f best '".$raw_url."' 2&1",$url);
		
		if(count($url) > 0){
			/*
			$streams = explode(",",$info['url_encoded_fmt_stream_map']); //the video's location info
			//foreach($streams as $stream){
			parse_str($streams[0],$data); //decode the stream
			$url = $data['url'];
			$params = explode("&",$data['url']);
			$dur = 0;
			foreach($params as $param){
				if (substr($param, 0, 3) === 'dur'){
					$dur = floor(explode("=", $param)[1]);
				}
			}
			*/
			$time = calcTime($start, $end, null);
			
			if($time[0]){
				$randName = "img/".bin2hex(random_bytes(5))."_".time().".mp4";
				
				$start = $time[1];
				$length = $time[2];
				$st = 0;
				
				exec("/usr/bin/ffmpeg -ss ".$start." -i '".$url[0]."' -t ".$length." -acodec copy -vcodec copy -async 1 -y ".$randName." 2>&1", $output);
				foreach($output as $resp){
					if(trim($resp) == "Metadata:"){
						$st = 1;
						break;
					}
				}
				if($st == 1){
					echo json_encode(["status" => 1, "dlink" => $randName]);
				}
				else{
					echo json_encode(["status" => 0, "Msg" => "An Error has occured. Can't crop video."]);
				}
				
			}
			else{
				echo json_encode(["status" => 0, "Msg" => $errors[$time[1]]]);
			}
		}
		else{
			echo json_encode(["status" => 0, "Msg" => "Invalid URL. Try again."]);
		}
			
	}
}

