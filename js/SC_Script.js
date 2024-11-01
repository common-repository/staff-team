var no_image = "";
function paginate(page, tpages) {
	if (tpages < 2)
      return '';
    var adjacents = 2;
    var prevlabel = "&lsaquo; "+contLDomain.paginate.prev;
    var nextlabel = contLDomain.paginate.next + " &rsaquo;";
    var out = "";
	out += "<ul>";
    if (page > adjacents + 1)
      out += "<li><a class='paginate'  href='#' id='1'> << </a>\n</li></ul>";
    if (page == 1) {
      out += "<li><span>" + prevlabel + "</span></li>\n";
    } 
	else{
        if (page == 2) { out += "<li><a class='paginate' href='#' id = '1'>" + prevlabel + "</a>\n</li>";} 
		else { out += "<li><a class='paginate' href='#' id='" + (page - 1) + "'>" + prevlabel + "</a>\n</li>";}
    }
    var pmin = (page > adjacents) ? (page - adjacents) : 1;
    var pmax = (page < (tpages - adjacents)) ? (page + adjacents) : tpages;
    for (var i = pmin; i <= pmax; i++) {
        if (i == page) { out += "<li class='active_pg'><span>" + i + "</span></li>\n";
        } 
		else {
            if (i == 1) { 
				out += "<li><a class='paginate' href='#' id='1'>" + i + "</a>\n</li>";
            } 
			else { out += "<li><a class='paginate' href='#'id='" + i + "'>" + i + "</a>\n</li>";
            }
        }
    }
    if (page < tpages) { out += "<li><a class='paginate' href='#' id='" + (page + 1) + "'>" + nextlabel + "</a>\n</li>";
    } 
	else { out += "<li><span>" + nextlabel + "</span></li>\n";
    }
    if (page < (tpages - adjacents)) { out += "<li><a class='paginate'  href='#' id='" + tpages + "'> >> </a>\n</li>";
    }
	out += "</ul>";
    return out;
}

/*--- Full ---*/
function getContainerFull(start, end, arr,lightbox, fb_link, ins_link, tw_link, gp_link,no_image) {
    var out = '';
    for (var i = start; i < end; i++) {
		out += '<div class="staff_contact full_view">';
			out += '<div class="left_image">';	
				out += '<div class="staff_overlay">';
					out += '<div class="staff_more_inform"><div class="staff_more_info_plus open_popup" data-id="'+ arr[i]['id']+'"></div></div>';
				out += '</div>';
				if(arr[i]['img']['thumb'].indexOf('noimage.jpg')==-1 || no_image==""){
					out += '<div class="staff_image_border"><span class="full_cont_main_picture" role="img" aria-label="'+arr[i]['title']+'" style="display:block; background-image: url(\'' + arr[i]['img']['thumb'] + '\');"><img src="'+arr[i]['img']['thumb']+'" alt="'+arr[i]['title']+'" id="imagelightbox" style="display:none;"/></span></div>';
				}else {
					out += '<div class="staff_image_border"><div class="full_cont_main_picture" style="background-image: url(\'' + no_image + '\');"></div></div>';
				}
			out += '</div>';
			out += '<div class="right_content">';
				out += '<div class="staff_top_info">';
					out += '<a href="'+arr[i]['link']+'"><p class="staff_cont_name">'+ arr[i]['title']+'</p></a>';
					var params = arr[i]['params'];
					if(typeof (params)!="object"){
						params = [];
					}
					if(typeof params == 'object'){
						out += getShortParamsView(params,arr[i]['category']);
					}
				out += '</div>';
				out += '<div class="contact_content">'+arr[i]['description']+'</div>';
				out += '<div class="staff_full_bottom">';
					out += '<div class="staff_more_info_btn"><a href="'+arr[i]['link']+'">'+contLDomain.more_inf+'</a></div>';						
					out += '<div class="staff_soc_icons">';
						out += '<ul>';
							out += '<li><a class="staff_icon facebook" href="'+fb_link+'" target="_blank"><i class="fa fa-facebook"></i></a></li>';
							out += '<li><a class="staff_icon instagram" href="'+ins_link+'" target="_blank"><i class="fa fa-instagram"></i></a></li>';
							out += '<li><a class="staff_icon twitter" href="'+tw_link+'" target="_blank"><i class="fa fa-twitter"></i></a></li>';
							out += '<li><a class="staff_icon gplus" href="'+gp_link+'" target="_blank"><i class="fa fa-google-plus"></i></a></li>';
						out += '</ul>';
					out += '</div>';
				out += '</div>';
			out += '</div>';
		out += '</div>';
    }
    return out;
}
/*-- Popup_Full --*/
function getPopupFull(item,lightbox, fb_link, ins_link, tw_link, gp_link,enable_message){
	var out = '<div class="popup_info"><div class="stPopOut">';	
		out += '<div class="close_popup_circle"><span>X</span></div>';
		out += '<div class="staff_top_info">';
			out += '<span class="staff_cont_name">'+item['title']+'</span>';
			var params = item['params'];
			if(typeof (params)!="object"){
				params = [];
			}
			if(typeof params == 'object'){
				out += getShortParamsView(params,item['category']);
			}
		out += '</div>';
		
		if(item['img']['thumb'].indexOf('noimage.jpg')==-1){
			out += '<div class="leftPart">';
				/*image*/
				out += '<div class="left_image">';
					out += '<a href="' + item['img']['full'] + '"'; if(lightbox){ out += 'rel="contact_lightbox"'; } else { out +='target="_blank"'; } out += 'class="cont_main_picture_a" style="text-decoration:none;">';
						out += '<div class="staff_image_border"><span class="full_cont_main_picture" role="img" aria-label="'+item['img']['title']+'" style="display:block;background-image: url(\'' + item['img']['thumb'] + '\');"><img src="'+item['img']['thumb']+'" alt="'+item['img']['title']+'" id="imagelightbox" style="display:none;"/></span></div>';
					out += '</a>';
				out += '</div>';
				/*icons*/
				out += '<div class="staff_full_bottom">';
					out += '<div class="staff_soc_icons">';
						out += '<ul>';
							out += '<li><a class="staff_icon facebook" href="'+fb_link+'" target="_blank"><i class="fa fa-facebook"></i></a></li>';
							out += '<li><a class="staff_icon instagram" href="'+ins_link+'" target="_blank"><i class="fa fa-instagram"></i></a></li>';
							out += '<li><a class="staff_icon twitter" href="'+tw_link+'" target="_blank"><i class="fa fa-twitter"></i></a></li>';
							out += '<li><a class="staff_icon gplus" href="'+gp_link+'" target="_blank"><i class="fa fa-google-plus"></i></a></li>';
						out += '</ul>';
					out += '</div>';
					if(enable_message==1){
						out += '<div class="staff_more_info_btn"><a href="'+item['link']+'">'+contLDomain.send_email+'</a></div>';
					}
				out += '</div>';
			out += '</div>';
			/*content*/
			out += '<div class="right_content">';
				var params = item['params'];
				if(typeof (params)!="object"){
					params = [];
				}
				if(typeof params == 'object'){
					out += getFullParamsView(params,item['category'],item['mail']);
				}
				out += '<div class="popup_content">'+item['description_popup']+'</div>';
			out += '</div>';
		}
		else{
			out += '<div class="left_image" style="width:0;"></div>';
			out += '<div class="right_content" style="width:100%;">';
			var params = item['params'];
				if(typeof params == 'object'){
					out += getFullParamsView(params,item['category'],item['mail']);
				}
				out += '<div class="popup_content">'+item['description_popup']+'</div>';
				/*icons*/
				out += '<div class="staff_full_bottom noAb">';
					out += '<div class="staff_soc_icons">';
						out += '<ul>';
							out += '<li><a class="staff_icon facebook" href="'+fb_link+'" target="_blank"><i class="fa fa-facebook"></i></a></li>';
							out += '<li><a class="staff_icon instagram" href="'+ins_link+'" target="_blank"><i class="fa fa-instagram"></i></a></li>';
							out += '<li><a class="staff_icon twitter" href="'+tw_link+'" target="_blank"><i class="fa fa-twitter"></i></a></li>';
							out += '<li><a class="staff_icon gplus" href="'+gp_link+'" target="_blank"><i class="fa fa-google-plus"></i></a></li>';
						out += '</ul>';
					out += '</div>';
					if(enable_message==1){
						out += '<div class="staff_more_info_btn"><a href="'+item['link']+'">'+contLDomain.send_email+'</a></div>';
					}
				out += '</div>';
			out += '</div>';
		}
	out += '</div></div>';
	return out;
}
/*- Animation_Full -*/
setTimeout(function(){
  jQuery(".staff_contact.full_view").hover(function(){
	jQuery(".staff_more_info_plus").addClass("staff_zoom staff_animate");
  }, 
  function() {
	jQuery(".staff_more_info_plus").removeClass("staff_zoom staff_animate");
  });
},100);

function getFullParamsView(params,mail) {
    var out = '<div class="full_params_div"><table class="full_params" border="1">';
		//out += '<tr><td class="param_name">' + contLDomain.category + ':</td>';
        //out += '<td class="param_value"><a href="mailto:' + mail + '">'+mail+'</a></td></tr>';
		jQuery.each(params, function (index, value) {
        for (var i = 0; i < value.length; i++) {
            if (value[i].length != 0) {
                if (i == 0) {
                    out += '<tr><td class="param_name">' + index + ':</td>';
                    out += '<td class="param_value">' + value[i] + '</td></tr>';
                }
                else { out += '<tr><td></td> <td class="param_value no-border">' + value[i] + '</td></tr>'; }
            }
        }
    });
    out += '</table></div>';
    return out;
}

function getShortParamsView(params,category) {
    var out = '<p class="staff_category">' + category + '</p>';
    return out;
}

function findItem(term, arr,search_type) {
    var items = [];
    for (var i = 0; i < arr.length; i++) {
		var item = arr[i];
        term = term.toLowerCase();
		if(typeof (item["description"])==="undefined"){
			item["description"] = "";
		}
		var desc = item["description"].toString().toLowerCase();
			switch (search_type){
				case 3:
					var detail = item['title'].toString().toLowerCase();
					var cats = item['category'].toString().toLowerCase();
					if (detail.indexOf(term) > -1 || cats.indexOf(term) > -1 || desc.indexOf(term) > -1) {
						items.push(item);
					}
				break;
				case 1:
					var detail = item['category'].toString().toLowerCase();
					if (detail.indexOf(term) > -1 || desc.indexOf(term) > -1) {
						items.push(item);
					}
				break;
				default :
					var detail = item['title'].toString().toLowerCase();
					if (detail.indexOf(term) > -1 || desc.indexOf(term) > -1) {
						items.push(item);
					}
				break;
			}
    }
    return items;
}

var contactView = function (arrConts, step, theme, type,search_type,lightbox ,fb_link, ins_link, tw_link, gp_link, enable_message, no_image ,staff_uniqid) {
    var start = 0;
    var arrCat = arrConts;
    var arrItems = arrCat;
    var step = step;
    function starttable(arr) {
		start = 0;
		end = start + step;
		length = arr.length;
		tpages = parseInt(length / step);
		if (tpages == 0)
			end = length;
		if (length % step > 0)
			tpages++;
		out = getContainer(start, end, arr);
		jQuery('.' + theme + ' #'+type+'_contact .staff_sc_container.staff_'+staff_uniqid).html(out);
		if (tpages > 1)
			jQuery('.' + theme + ' #'+type+'_pgnt .staff_pagination.pagination_'+staff_uniqid).html(paginate(1, tpages));
		return false;
    }
	function getItemById(id) {
		for (var i = 0; i < arrConts.length; i++) {
			var item = arrConts[i];
			if(item.id == id){
				return item;
			}
		}
		return false;
	}
    starttable(arrItems);
	
	/*- Pagination -*/
	jQuery("body").on("click", '.' + theme + ' #'+type+'_pgnt .pagination_'+staff_uniqid+' .paginate ', function () {
		starttable(arrItems);
		var p = parseInt(jQuery(this).attr('id'));
		start = step * (p - 1);
		if (start + step <= length)
			out = getContainer(start, start + step, arrItems);
		else
			out = getContainer(start, length, arrItems);
		jQuery('.' + theme + ' #'+type+'_contact .staff_sc_container.staff_'+staff_uniqid).html(out);
		jQuery('.' + theme + ' #'+type+'_pgnt .staff_pagination.pagination_'+staff_uniqid).html(paginate(p, tpages));
		return false;
	});
	
    /*- Search -*/
	function  search() {
		var val = jQuery('.' + theme + ' #'+type+'_search.search_'+staff_uniqid+' .search_cont').val();
		if (val.length < 1) {
			arrItems = arrCat;
			starttable(arrItems);
			return false;
		}
		var objItems = findItem(val, arrCat,search_type);
		var itmLength = objItems.length;
		length = itmLength;
		start = 0;
		arrItems = objItems;
		end = start + step;
		var totpages = parseInt(itmLength / step);
		if (itmLength % step > 0)
			totpages++;
		if (totpages == 0)
			end = itmLength;

		tpages = totpages;
		if (start + step <= itmLength)
			out = getContainer(start, end, arrItems);
		else
			out = getContainer(start, itmLength, arrItems);
		jQuery('.' + theme + ' #'+type+'_contact .staff_sc_container.staff_'+staff_uniqid).html(out);
		jQuery('.' + theme + ' #'+type+'_pgnt .staff_pagination.pagination_'+staff_uniqid).html(paginate(1, totpages));
		return false;
	}
	jQuery('.' + theme + ' #'+type+'_search.search_'+staff_uniqid+' .search_button').click(function () {
		search();
	});
	jQuery('.' + theme +' #'+type+'_search.search_'+staff_uniqid+' .search_reset').click(function () {
		jQuery('.' + theme + ' #'+type+'_search.search_'+staff_uniqid+' .search_cont').val('');
		search();
	});
	jQuery('.' + theme + ' #'+type+'_search.search_'+staff_uniqid+' .search_cont').bind('keyup', function (event){
		if(event.keyCode==13){
			search();
		}
		var search_cont = jQuery(".search_cont");
		if(search_cont.val().length>3){
			search();
		}else if(search_cont.val().length === 0){
            search();
        }
	});
	
	
	
	/*- SHOW POPUP -*/
	jQuery('.' + theme + ' #popup').hide();
	jQuery('.' + theme + ' #'+type+'_contact').on('click','.open_popup', function (e){		
		/*for not scrolling body*/
		jQuery('body').css("height",jQuery( window ).height());
		jQuery('body').css("overflow","hidden");

		jQuery("#theme").attr('id', 'popup_back');
		jQuery('.staff_sc_container').addClass('staff_blurred_on');
		jQuery('.popup').addClass('staff_zoom staff_animate');
		
		var objId = jQuery(this).data('id');
		var item = getItemById(objId);
		var out = '';
		 switch (type) {
			case 'full':
				out = getPopupFull(item,lightbox, fb_link, ins_link, tw_link, gp_link,enable_message);
				break;
		}
		/*not open all popups at same time*/
		var parent_id = jQuery(this).closest("div.parentDiv").attr("id");;
		jQuery('.' + theme + ' #'+parent_id+' #popup').html(out);
		jQuery('.' + theme + ' #'+parent_id+' #popup').show();	
		/*-for slide popup-*/
		setTimeout(function(){
			jQuery('.popup_info').addClass('slide_popup speed_05');
		},1);
	});	

	/*-HIDE POPUP-*/
	jQuery('.' + theme + ' #'+type+'_contact').on('click','.popup',function(e){
		if(jQuery(e.target.closest(".popup_info")).length != 0) return;
		jQuery('.' + theme + ' #popup').hide(200);
		jQuery( ".popup_back" ).remove();
		jQuery('.staff_sc_container').removeClass('staff_blurred_on');
		jQuery('body').css("overflow","auto");
	});
	jQuery('.' + theme + ' #'+type+'_contact').on('click','.close_popup_circle,.close_popup_square', function (e){
		jQuery('.' + theme + ' #popup').hide(200);
		jQuery( ".popup_back" ).remove();
		jQuery('.staff_sc_container').removeClass('staff_blurred_on');
		jQuery('body').css("overflow","auto");
	});
	jQuery(document).ready(function(){
		jQuery(document).bind('keydown', function(e) { 
			if (e.which == 27) {
				jQuery('.' + theme + ' #popup').hide(200);
				jQuery( ".popup_back" ).remove();
				jQuery('.staff_sc_container').removeClass('staff_blurred_on');
				jQuery('body').css("overflow","auto");
			}
		}); 
	});	
	
    function getContainer(start, end, arrItems) {
        var out = '';
        switch (type) {
            case 'short':
                out = getContainerShort(start, end, arrItems,lightbox,fb_link, ins_link, tw_link, gp_link, no_image);
                break;
            case 'full':
                out = getContainerFull(start, end, arrItems,lightbox,fb_link, ins_link, tw_link, gp_link,no_image);
                break;
            case 'table':
                out = getContainerTable(start, end, arrItems,lightbox,no_image);
                break;
			case 'chess':
                out = getContainerChess(start, end, arrItems,lightbox,fb_link, ins_link, tw_link, gp_link,no_image);
                break;
			case 'Portfolio':
                out = getContainerPortfolio(start, end, arrItems,lightbox,no_image);
                break;	
			case 'blog':
                out = getContainerBlog(start, end, arrItems,lightbox,no_image);
                break;
			case 'circle':
                out = getContainerCircle(start, end, arrItems,lightbox,no_image);
                break;
				
			case 'square':
                out = getContainerSquare(start, end, arrItems,lightbox,fb_link, ins_link, tw_link, gp_link);
                break;
        }
        return out;
	}
}

function mess_res() {}
var elsment_append = true;
/*-- Lightbox --*/
var activityIndicatorOn = function(){
	if(jQuery(window).width()>768)
		jQuery( '<div id="imagelightbox-loading"><div></div></div>' ).appendTo( 'body' );
},
activityIndicatorOff = function(){
	if(jQuery(window).width()>768)
		jQuery( '#imagelightbox-loading' ).remove();
},
overlayOn = function(arr){
	if(jQuery(window).width()>768 && elsment_append)
		jQuery( '<div id="imagelightbox-overlay"></div>' ).appendTo( 'body' );
	elsment_append = false;
},
overlayOff = function(){
	if(jQuery(window).width()>768)
		jQuery( '#imagelightbox-overlay' ).remove();
	elsment_append = true;
}

jQuery(document).ready(function(){
    jQuery( 'a[rel="contact_lightbox"]' ).imageLightbox({
		onStart: 	 function() { overlayOn();},
		onEnd:  	 function() { overlayOff(); activityIndicatorOff(); },
		onLoadStart: function() { activityIndicatorOn(); },
		onLoadEnd:	 function() { activityIndicatorOff(); },
		quitOnImgClick: true,
		quitOnDocClick: true,
		preloadNext:    false
    });
    jQuery('.teamsendbutton').on('click',function(){
		team_submit_message(jQuery(this).parents('#message_div'));
    });
    jQuery('.cont_mess_captcha_ref').on('click',function(){
       refreshCaptchaCont(jQuery(this).parents('#message_div'));
       return false;
    });

    jQuery('.teamsendbutton').closest('form').on('submit', function(){
        var pp_checkbox = jQuery("#twd_pp_checkbox");
        if((pp_checkbox.length > 0 && pp_checkbox.prop("checked"))||pp_checkbox.length===0){
            return true;
        }else{
            pp_checkbox.focus();
            alert(contLDomain.mess_text[6]);
            return false;
        }
    });



    jQuery('.mess_result').each(function(){
        if(jQuery(this).html() != ''){
           window.scrollTo(0,jQuery(this).offset().top-30);
        }
    });
	
});

