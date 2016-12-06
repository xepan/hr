<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr {

	class page_document extends \Page {
		public $title='Page Title';

		function init(){
			parent::init();

			// $file = $this->add('xepan\hr\Model_File');
			// $this->add('Grid')->setModel($file);
				// as per page 
			// http://codepen.io/kaizoku-kuma/pen/JDxtC
			$this->app->jui->addStylesheet('codemirror/codemirror-5.15.2/lib/codemirror');
			$this->app->jui->addStylesheet('codemirror/codemirror-5.15.2/theme/solarized');
			// $this->app->jui->addStylesheet('theme');

			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/lib/codemirror');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/htmlmixed/htmlmixed');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/jade/jade');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/php/php');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/xml/xml');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/css/css');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/javascript/javascript');
			

			$this->add('View',null,null,['page\xepandocument'])->set('document ');
			// $this->js(true,'

			// 		elFinder.prototype.i18.en.messages["cmdshare"] = "Share";          
			// 	    elFinder.prototype._options.commands.push("share");
			// 	    elFinder.prototype.commands.share = function() {
			// 	        this.exec = function(hashes) {
			// 	             //do whatever
			// 	        	$.univ().frameURL("Share","index.php?page=xepan_hr_share\&cut_page=1\&file_id="+this.files(hashes));
			// 	        	// console.log(hashes);
			// 	        	// alert("share");
			// 	        }
			// 	        this.getstate = function() {
			// 	            //return 0 to enable, -1 to disable icon access
			// 	            return 0;
			// 	        }
			// 	    }

			// 		$("#'.$this->name.'").elfinder({
			// 			url: "index.php?page=xepan_hr_test1",
			// 			height:450,
			// 			contextmenu : {
			// 	            // navbarfolder menu
			// 	            navbar : ["open", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "info","|","share"],
			// 	            // current directory menu
			// 	            cwd    : ["reload", "back", "|", "upload", "mkdir", "mkfile", "paste", "|", "sort", "|", "info"],
			// 	            // current directory file menu
			// 	            files  : ["getfile", "|", "share", "quicklook", "|", "download", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "edit", "rename", "resize", "|", "archive", "extract", "|", "info"]
			// 	        },
			// 			commandsOptions: {
			// 				edit : { 
			// 					// list of allowed mimetypes to edit // if empty - any text files can be edited mimes : [],
			// 					// you can have a different editor for different mimes 
			// 					editors : [{
			// 						mimes : ["text/plain", "text/html","text/x-jade", "text/javascript", "text/css", "text/x-php", "application/x-httpd-php", "text/x-markdown", "text/plain", "text/html", "text/javascript", "text/css"],
			// 						load : function(textarea) {
			// 							this.myCodeMirror = CodeMirror.fromTextArea(textarea, { 
			// 																			lineNumbers: true,
			// 																			theme: "solarized",
			// 																			viewportMargin: Infinity, 
			// 																			lineWrapping: true, 
			// 																			mode:"javascript",json:true,
			// 																			mode:"css",css:true , 
			// 																			htmlMode: true
			// 																		});
			// 						},
			// 						close : function(textarea, instance) { 
			// 							this.myCodeMirror = null; 
			// 						},
			// 						save : function(textarea, editor) {
			// 							textarea.value = this.myCodeMirror.getValue(); 
			// 						}
			// 					}] //editors 
			// 				} //edit
			// 			} //commandsOptions 
			// 		}).elfinder("instance");
			// 	');



			// Show filemanager

		}
	}

	// function defaultTemplate(){
	// 	return ['page\xepandocument'];
	// }

}
