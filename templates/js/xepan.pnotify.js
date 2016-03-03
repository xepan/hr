$.each({
	notify: function(xTitle, xText, xType, isDesktop, callback, sticky){
		var nn = new PNotify(
			{ 
				title: xTitle,
				text: xText,
				type: xType==null?"notice":xType,
				desktop: {
					desktop: isDesktop==null?false:true
				}
			}
			);
		if(callback !==undefined) nn.get().click(callback);	
		if(sticky !== null) {
			var nn = new PNotify(
			{ 
				title: xTitle,
				text: xText,
				type: xType==null?"notice":xType,
				hide: false,
				desktop: {
						desktop: false
					}
				}
			);
		}else{
			var nn = new PNotify(
			{ 
				title: xTitle,
				text: xText,
				type: xType==null?"success":xType,
				history: {
        			menu: true
    			}
    		}
			);
		}
	},
	
	getNotification: function (xUrl){
		$.ajax({
			url: xUrl,
		})
		.done(function(ret) {
			try{
				msg = JSON.parse(ret);
				if(msg['message']){
					$.univ().notify(msg['title'], msg['message'],msg['type'],true,undefined,msg['sticky']);
				}
				$.univ().getNotification(xUrl);
			}catch(e){

			}
		})
		.fail(function() {
			// console.log("error");
		})
		.always(function() {
			// console.log("complete");
		});		
	}
	
}, $.univ._import);