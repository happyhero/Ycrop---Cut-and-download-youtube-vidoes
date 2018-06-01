<DOCTYPE! html>
<html>
<head>
<title>YCrop - Cut And Download YouTube Videos</title>
<meta name="Description" content="Ycrop is simple and easy site to cut and download any youtube videos with the highest quality available.">
<meta name="Keywords" content="youtube crop, youtube cut, youtube download, youtube download videos, crop youtube vidoes">
<link href="css/style.css" rel="stylesheet">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/font-awesome.min.css" rel="stylesheet">
</head>
<body>

<header>
<b>Ycrop.me</b>
</header>
<div class="container">
 
	<div class="lside">
    <div class="input-group sbar">
      <input type="text" class="form-control" id="url" placeholder="Enter YouTube Url">
      <span class="input-group-btn">
        <button class="btn btn-primary" id="vsearch" type="button">GO</button>
      </span>
    </div>
	<br/>
  <div id="preview"></div>
  <div class="errDisp"></div>
	</div>
	<div class="rside">
	<h3>Y YCrop?</h3>
	I dont'crop YouTube videos often but once in a while I like to share a tiny segment of a video through Instagram or Snapchat
	and end up looking for an app that does it but all the apps that promise to do the cropping end up not working the way I want. They usually
	just embed a YouTube video with your desired start and end times and give you a link to it. I want to download the video not a link to share so I
	put up this simple cropper with a little bit of effort. All you have to do is just enter the start and end time using the preview and hit crop.
	</div>
	

</div>
<div class="footer">
  App by <a href="https://github.com/magna25">Henok Hailemariam</a>
</div>
</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>

function request(url, stt, ett) {
	
    $.ajax({
        type: "POST",
        url: "video.php",
		data: {url:url, st:stt, et:ett},
        dataType: "json",
        cache: false,
        success: function(data) {
			if(data.status == 1){
				$(".lside").html('<div class="card">   <h5 class="card-header bg-success">Success</h5>   <div class="card-body">     <p class="card-text">Thanks for using Ycrop.me, your video has been cropped and is ready for download.</p>     <a href="'+data.dlink+'" download class="btn btn-primary">Download</a>  <a class="btn btn-default" href="">Crop Another</a> </div> </div>');
			}
			else{
				$('.errDisp').fadeOut();
				$('.crp').attr("disabled",false).html('Cut');
				$('.errDisp').fadeIn().html("<br/><div class='alert alert-danger'>"+data.Msg+"</div>");
			}
			console.log(data);
        },
        error: function(error) {
			console.log(error)
		}
    })
}

$("#vsearch").click(function(){
	showPreview();
})

$('#url').on('keypress', function(e) {
    var code = e.keyCode || e.which;
    if(code==13){
       showPreview();
    }
});	

function showPreview(){
	var err = "<div class='alert alert-danger'>Invalid YouTube URL.</div>";
	var url = $('#url').val();
	if(url == ""){
		$(".errDisp").html(err);
	}
	else if(url.match('https://(www.)?youtube|youtu\.be')){
		$(".errDisp").hide();
		id=url.split(/v\/|v=|youtu\.be\//)[1].split(/[?&]/)[0];
		$("#preview").html('<iframe id="ytplayer" type="text/html"   src="https://www.youtube.com/embed/'+id+'?autoplay=1" frameborder="0"></iframe>');
		$("#preview").append("<div class='cntrls'>Start at: <input id='st' placeholder='00:00' value='00:00' type='text'> End at: <input id='et' placeholder='00:00' value='00:00' type='text'> <button class='btn btn-success btn-sm crp'>Cut</div>");
	}
	else{
		$(".errDisp").html(err);
	}
}
$('body').on('click', '.crp', function() {
	$(this).attr("disabled",true).html('<i class="fa fa-circle-o-notch fa-spin"></i>');
	var st = $('#st').val();
	var et = $('#et').val();
	var url = $('#url').val();
	
	if(!st.match(/^(?:1[0-2]|0[0-9]:)?[0-5][0-9]:[0-5][0-9]/) || !et.match(/^(?:1[0-2]|0[0-9]:)?[0-5][0-9]:[0-5][0-9]/)){
		$(this).attr("disabled",false).html("Cut");
		$(".errDisp").show().html("<br/><div class='alert alert-danger'>Invalid Time Format</div>");
	}
	else{
		request(url, st, et);
	}
	
});
</script>


