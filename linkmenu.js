

var OrcpLinkMenu = function(btn_id, menu_id, data)
{
	this.elm;
	this.btn_id = btn_id;
	this.menu_id = menu_id;

	this.on_btn = false;
	this.on_menu = false;
	this.enter_menu = false;

	this.menu_disp  = false;

	this.menu_x = 0;
	this.menu_x_org = 0;
	this.menu_y = 0;

	this.menu_num = 0;
	this.disp_first_time = true;

	this.GetPosition();
	this.MakeLinkMenu(data);
}

OrcpLinkMenu.prototype.GetPosition = function()
{
	var left = 0;
	var top = 0;
	var el = jQuery('#'+this.btn_id);
//alert(dump(el));
/*alert(el);

jQuery('#'+this.btn_id).offset(function(index, coords) {
alert(index);
alert(coords.left);
return {left: 999, top:999}; //<-- you need to return this.
}); 
*/

	left = jQuery(el).offset().left;
	top = jQuery(el).offset().top;
//alert(jQuery(el).height()); alert(parseInt(jQuery(el).css('padding-top'))); alert(parseInt(jQuery(el).css('margin-top')));
	var height = jQuery(el).height();
	if (!isNaN(parseInt(jQuery(el).css('padding-top')))) height += parseInt(jQuery(el).css('padding-top'));
	if (!isNaN(parseInt(jQuery(el).css('margin-top')))) height += parseInt(jQuery(el).css('margin-top'));
//alert(height+'**');
	this.menu_y = Math.floor(top) + height;
	this.menu_x = Math.floor(left);
	this.menu_x_org = this.menu_x;
}


OrcpLinkMenu.prototype.MakeLinkMenu = function(data)
{
	var html = "<ul>";

	html = this.MakeLinkMenu_sub(0, 0, 9, data, html);

	html=html+"</ul>";

	var el = document.createElement('div');
	el.id = this.menu_id;
	document.getElementsByTagName('body')[0].appendChild(el);
	jQuery('#'+this.menu_id).css('display','none');
	jQuery('#'+this.menu_id).css('top',this.menu_y+"px");
	jQuery('#'+this.menu_id).css('left',this.menu_x+"px");

	jQuery('#'+this.menu_id).html(html);
	this.org_height = jQuery('#'+this.menu_id).height();
	jQuery('#'+this.menu_id).addClass('pgcatmenu');
	jQuery('#'+this.menu_id).css('position','absolute');
	jQuery('#'+this.menu_id).css('border','solid '+pgcatmenu_border_size+'px'+' '+pgcatmenu_border_color);
	jQuery('#'+this.menu_id).css('background-color', pgcatmenu_background_color);
//alert(jQuery(window).height());

	var that = this;
	jQuery('#'+this.btn_id).hover(
		function() {
			if (!that.menu_disp) {
				jQuery('#'+that.menu_id).show();
				that.AdjustDimension();
				that.menu_disp = true;
			}
			that.on_btn = true;
			that.enter_menu = false;
		},
		function() {
			that.on_btn = false;
			if (!that.on_menu/* && that.enter_menu*/) {
				if (that.menu_disp) {
					jQuery('#'+that.menu_id).hide();
					that.menu_disp = false;
				}
			}
		}
	);
	jQuery('#'+this.menu_id).hover(
		function() {
			if (!that.menu_disp) {
				jQuery('#'+that.menu_id).show();
				that.AdjustDimension();
				that.menu_disp = true;
			}

			that.on_menu = true;
			that.enter_menu = true;
		},
		function() {
			that.on_menu = false;
			if (!that.on_btn && 	that.enter_menu) {
				jQuery('#'+that.menu_id).hide(); 
				that.menu_disp = false;
			}
		}
	);
}

OrcpLinkMenu.prototype.MakeLinkMenu_sub = function(parent, level, max_level, data, html)
{
	var i;

	if (level > max_level) {
		return html;
	}
	var spc="";
	for (i=0; i<level*2; i++) {
		spc = spc + "&nbsp;";
	}
	for (i=0; i<data.length; i++) {
		if (parent == data[i]['parent']) {
			html=html+'<div style="display:block" id="'+this.menu_id+'_'+this.menu_num++ +'">'+spc+'<a style="font-size:'+pgcatmenu_font_size+'px" href="' + data[i]['url']+ '">' + data[i]['title'] + '</a></div>\n';
			html = this.MakeLinkMenu_sub(data[i]['ID'], level+1, max_level, data, html);
		}
	}
	return html;
}



OrcpLinkMenu.prototype.AdjustDimension = function()
{
//return;
	d="";
	var max_width = 0;
	var height=0;
	for (i=0;i<this.menu_num;i++){
		var id='#'+this.menu_id+'_'+i;
		if (jQuery(id).width() > max_width) max_width = jQuery(id).width(); 
		if (!height) height=jQuery(id).height();
		jQuery(id).css('position','absolute');
		jQuery(id).css('border','solid 0px #f00');
		d=d+id+"  "+jQuery(id).position().top+"  "+jQuery(id).width()+' '+jQuery(id).height()+' '+jQuery(id).css('border-top')+' '+jQuery(id).css('margin-top')+' '+jQuery(id).css('padding-top');;
		d=d+"\n";
	}

	var x=pgcatmenu_padding_size_x;
	var y=pgcatmenu_padding_size_y;
	var max_y = 0;

	for (i=0; i<this.menu_num; i++) {
		var id='#'+this.menu_id+'_'+i;
		if (this.disp_first_time) { // avoid growing 1 dot everytime when displaying menu.
			jQuery(id).width(max_width+1);
		}
		this.disp_first_time = false;

		if (this.menu_y+y+height+
			pgcatmenu_padding_size_y + pgcatmenu_line_spacing + pgcatmenu_border_size
				> jQuery(window).height() + jQuery(document).scrollTop()) {
			y=pgcatmenu_padding_size_y;
			x += max_width + pgcatmenu_padding_size_c;
		}

		jQuery(id).css('top', y);
		jQuery(id).css('left', x);
		if (y>max_y) max_y=y;
		y += height + pgcatmenu_line_spacing;
	}

//alert(d);
	jQuery('#'+this.menu_id).height(max_y + height + pgcatmenu_padding_size_y );
	var width = x+max_width + pgcatmenu_padding_size_x;
	jQuery('#'+this.menu_id).width(width);
//alert("width=" + jQuery('#'+this.menu_id).width() + "position=(" + jQuery('#'+this.menu_id).css('left')+','+jQuery('#'+this.menu_id).css('top'));
	this.menu_x = this.menu_x_org;
	if (this.menu_x + width > jQuery(window).width()) {
		this.menu_x = jQuery(window).width() - width;
		if (this.menu_x<0) this.menu_x=0;
	}
	jQuery('#'+this.menu_id).css('left',this.menu_x+"px");

}

function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

