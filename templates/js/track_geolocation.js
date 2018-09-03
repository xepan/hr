xepan_track_geolocation_time = 36000000;// 10 minutes

function xepan_track_geolocation_error(data){
	console.log(data);
}

function xepan_track_geolocation(position){
	console.log(position);
	$.ajax({
		url: 'index.php?page=xepan_hr_trackgeolocation',
		type: 'POST',
		dataType: 'json',
		data: {geodata: position},
	})
	.done(function(result) {
		eval(result);
		// console.log("success");
	})
	.fail(function() {
		// console.log("error");
	})
	.always(function() {
		// console.log("complete");
	});
	
}

function xepan_geolocation_loop(){
	setTimeout(navigator.geolocation.getCurrentPosition(xepan_track_geolocation, xepan_track_geolocation_error),3000);
	setTimeout(xepan_geolocation_loop,xepan_track_geolocation_time);
}

$.each({
	xepan_track_geolocation: function(timeout){
		xepan_track_geolocation_time = timeout;

		if (navigator.geolocation) {
			xepan_geolocation_loop();
		} else {
		  error('not supported');
		}
	},

	xepan_geolocation_loop: function(){

	}
},$.univ._import);