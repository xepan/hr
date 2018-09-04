xepan_track_geolocation_time = 36000000;// 10 minutes

// setTimeout override for inactive tabs

var $momentum;

    function createWorker() {
        var containerFunction = function () {
            var idMap = {};

            self.onmessage = function (e) {
                if (e.data.type === 'setInterval') {
                    idMap[e.data.id] = setInterval(function () {
                        self.postMessage({
                            type: 'fire',
                            id: e.data.id
                        });
                    }, e.data.delay);
                } else if (e.data.type === 'clearInterval') {
                    clearInterval(idMap[e.data.id]);
                    delete idMap[e.data.id];
                } else if (e.data.type === 'setTimeout') {
                    idMap[e.data.id] = setTimeout(function () {
                        self.postMessage({
                            type: 'fire',
                            id: e.data.id
                        });
                        // remove reference to this timeout after is finished
                        delete idMap[e.data.id];
                    }, e.data.delay);
                } else if (e.data.type === 'clearCallback') {
                    clearTimeout(idMap[e.data.id]);
                    delete idMap[e.data.id];
                }
            };
        };

        return new Worker(URL.createObjectURL(new Blob([
            '(',
            containerFunction.toString(),
            ')();'
        ], {type: 'application/javascript'})));
    }

    $momentum = {
        worker: createWorker(),
        idToCallback: {},
        currentId: 0
    };

    function generateId() {
        return $momentum.currentId++;
    }

    function patchedSetInterval(callback, delay) {
        var intervalId = generateId();

        $momentum.idToCallback[intervalId] = callback;
        $momentum.worker.postMessage({
            type: 'setInterval',
            delay: delay,
            id: intervalId
        });
        return intervalId;
    }

    function patchedClearInterval(intervalId) {
        $momentum.worker.postMessage({
            type: 'clearInterval',
            id: intervalId
        });

        delete $momentum.idToCallback[intervalId];
    }

    function patchedSetTimeout(callback, delay) {
        var intervalId = generateId();

        $momentum.idToCallback[intervalId] = function () {
            callback();
            delete $momentum.idToCallback[intervalId];
        };

        $momentum.worker.postMessage({
            type: 'setTimeout',
            delay: delay,
            id: intervalId
        });
        return intervalId;
    }

    function patchedClearTimeout(intervalId) {
        $momentum.worker.postMessage({
            type: 'clearInterval',
            id: intervalId
        });

        delete $momentum.idToCallback[intervalId];
    }

    $momentum.worker.onmessage = function (e) {
        if (e.data.type === 'fire') {
            $momentum.idToCallback[e.data.id]();
        }
    };

    window.$momentum = $momentum;

    window.setInterval = patchedSetInterval;
    window.clearInterval = patchedClearInterval;
    window.setTimeout = patchedSetTimeout;
    window.clearTimeout = patchedClearTimeout;

// setTimeout override end


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
	navigator.geolocation.getCurrentPosition(xepan_track_geolocation, xepan_track_geolocation_error);
	setTimeout(xepan_geolocation_loop,xepan_track_geolocation_time);
}

$.each({
	xepan_track_geolocation: function(timeout){
		xepan_track_geolocation_time = timeout;

		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(xepan_track_geolocation, xepan_track_geolocation_error);
			setTimeout(xepan_geolocation_loop,xepan_track_geolocation_time);
			// xepan_geolocation_loop();
		} else {
		  error('not supported');
		}
	},

	xepan_geolocation_loop: function(){

	}
},$.univ._import);