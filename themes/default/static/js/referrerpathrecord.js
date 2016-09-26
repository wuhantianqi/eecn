;(function (root, factory) {
    if (typeof exports === 'object') {
        module.exports = factory();
    } else if (typeof define === 'function') {
        define(factory);
    } else {
        root.referrerPathRecord = factory();
        root.referrerPathRecord.init();
    }
})(window, function (require, exports, module) {
    
    var referrerPathRecord = {
    	referrerMap : {
    		referrer : {
    			other_from:{
	    			'baidu' : 'z1',
					'360' : 'z2',
					'sogou' : 'z3',
					'sm' : 'z4',
					'sem_bing' : 'z5',
					'sem_youdao' : 'z6',
					'mall_baidu' : 'j1',
					'mall_360' : 'j2',
					'mall_sogou' : 'j3',
					'mall_sm' : 'j4',
					utm_network :{
						'baidu' : 'r1',
						'mall_baidu' : 'e2'
					},
					'360RTB' :'r2',
					'gdt' :'r3',
					'sg_wm' :'r4',
					'jx_wm' :'r5',
					'google' :'r6',
					'baidu_image' :'r7',
					'sem_weibo' :'r8',
					'sina_fuyi' :'r9',
					'baidu_feeds' :'r10',
					'txzht' :'r11',
					'mall_sem_weibo' :'e1',
					'mall_gdt' :'e3',
					'mall_sg_wm' :'e4',
					'mall_jx_wm' :'e5',
					'mall_sina_fuyi' :'e6',
					'mall_baidu_feeds' :'e7',
					'mall_txzht' :'e8',
					'pc_baidupz' :'b1',
					'baidupz' :'b2'
	    		},
	    		to8to_from:{
	    			'2345': 'w1',
					'58tongcheng': 'w2',
					'toutiao': 'w3',
					'cpctg_souhu_2': 'w4'
	    		},
	    		'baidu.com': 'n1',
				'haosou.com': 'n2',
				'sogou.com': 'n3',
				'sm.cn': 'n4',
				'bing.com': 'n5',
				'google.com': 'n6',
				'google.cn': 'n7',
				'sina.cn': 'n8',
				'360.cn': 'n9',
				'2345.com': 'n10'
	    	},
	    	favorites : 'b3',
	    	inputDirectly : 'b4'
	    },
    	init: function(){
    		this.recordPath();
    		addEvent(window, 'beforeunload', function(event){
    			var n = event.screenX - window.screenLeft;
    			var b = n > Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)-20;
    			if(!(b&&event.clientY<0||event.altKey)){
    				setCookie('act', 'freshen', 5);
    			}
    		});
    	},
    	recordPath: function (){
	    	var act 	   =  getCookie('act'),
	    		pathCookie =  getCookie('sourcepath'),
	    		path       =  !pathCookie ? [] : pathCookie.split('|'),
	    		len        =  isFromPc()? 50 : 30,
	    		expires, refUrl, curUrl, code;
	    	if(act == 'freshen'){return;}
	    	try{
	    		refUrl = decodeURI(document.referrer);
	    		curUrl = decodeURI(location.href);
	    		expires = indexOfArray(['mall.to8to.com','m.to8to.com/mall'], location.hostname + location.pathname)>-1? 30*24*3600: 360*24*3600;
	    		code   = getReferrerCode(refUrl, curUrl, this.referrerMap);
	    	}catch(e){}
		    if(code){
		    	path = path.slice(-(len-1));
		    	path.push(code);
		    	setCookie('sourcepath', path.join('|'), expires);
		    } 
	    }
    };

    function getReferrerCode(refUrl, curUrl, referrerMap){
    	var referrer_from = getReferrerDomain(refUrl),
    		other_from = getQueryString(curUrl,'utm_from') ||
    						getQueryString(curUrl,'utm_source') ||
    							getQueryString(curUrl,'to8to_tgid'),
    		to8to_from = getQueryString(curUrl,'to8to_from'),
    		utm_network = getQueryString(curUrl,'utm_network'),
    		code = null;
    	if(refUrl){
    		if(refUrl.indexOf('to8to.com')>-1){return null;}
			if(other_from){
				if(other_from == 'baidu' && utm_network){
					code = referrerMap.referrer.other_from.utm_network[other_from];
				}else{
					code = referrerMap.referrer.other_from[other_from];
				}
			}else if(to8to_from){
				code = referrerMap.referrer.to8to_from[to8to_from];
			}else{
				code = referrerMap.referrer[referrer_from] || referrer_from;
			}
    	}else{
    		if(other_from||to8to_from){
    			code = referrerMap.favorites;
    		}else{
    			code = referrerMap.inputDirectly;
    		}
    	}
    	return code;
    }

    function setCookie(c_name, value, expires, domain) {
		var exdate = new Date();
		var main = arguments[3] ? '' : 'domain=.to8to.com';
		exdate.setTime(exdate.getTime() + (expires * 1000));
		var c_value = escape(value) + ((expires == null) ? "" : "; expires=" + exdate.toUTCString());
		document.cookie = c_name + "=" + c_value + ';path=/;'+main;
	}

	function getCookie(c_name) {
		var c_value = document.cookie;
		var c_start = c_value.indexOf(" " + c_name + "=");
		if (c_start == -1) {
			c_start = c_value.indexOf(c_name + "=");
		}
		if (c_start == -1) {
			c_value = null;
		} else {
			c_start = c_value.indexOf("=", c_start) + 1;
			var c_end = c_value.indexOf(";", c_start);
			if (c_end == -1) {
				c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start, c_end));
		}
		return c_value;
	}

	function getQueryString(url, name) {
	    var reg, r;
	    if(!name){
	    	name = url;
	    	url = window.location.search.substr(1);
	    }
	    reg = new RegExp("(^|&|\\?)" + name + "=([^&]*)(&|$)", "i");
	    r = url.match(reg);
	    if (r != null) return unescape(r[2]);
	    return null;
	}

	function getReferrerDomain(refUrl) {
	    var hostname = domain = '', a;
	    if(refUrl){
	    	a = document.createElement('a');
	    	a.href = refUrl;
	    	hostname = a.hostname;
	    	domain = hostname.split('.').slice(-2).join('.'); 

	    }
	    return domain;
	}

	function isFromPc(){
		var flag = true;
			ua = navigator.userAgent,
			agents = ['Android','iPhone','SymbianOS','Windows Phone','iPad','iPod'];
		for(var i=0,len=agents.length; i<len; i++){
			if(ua.indexOf(agents[i])>-1){
				flag = false;
				break;
			}
		}
		return flag;
	}

	function addEvent(element, type, handler){
		var fn = function(event) {
			event = event || window.event;
            handler.call(element, event);
        }
		if(element.addEventListener){
			element.addEventListener(type, fn, false);
		}else if(element.attachEvent){
			element.attachEvent('on'+type, fn);
		}else{
			element['on'+type] = fn;
		}
	}

	function indexOfArray(arr , item){
        for(var i = 0; i < arr.length; i++){
            if(item.indexOf(arr[i])>-1){
                return i;
            }
        }
        return -1; 
	}

    return referrerPathRecord;
});