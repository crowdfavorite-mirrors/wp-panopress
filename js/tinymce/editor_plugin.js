/** PanoPress v.1.2 | Developed by Omer Calev <http://www.omercalev.com/> | Code licensing & documentation <http://www.panopress.org/> **/
(function(){tinymce.create("tinymce.plugins.panopress",{init:function(a,b){a.addCommand("mce_open_win",function(){a.windowManager.open({file:b+"/popup.html",width:320+parseInt(a.getLang("panopress.delta_width",0)),height:240+parseInt(a.getLang("panopress.delta_height",0)),inline:1},{plugin_url:b})});a.addButton("pp_button",{title:"Embed Panorama",cmd:"mce_open_win",image:b+"/button.png"})},getInfo:function(){return{longname:"PanoPress",author:"The PanoPress team",authorurl:"http://www.panopress.org", infourl:"http://www.panopress.org",version:tinymce.majorVersion+"."+tinymce.minorVersion}}});tinymce.PluginManager.add("panopress",tinymce.plugins.panopress)})();
