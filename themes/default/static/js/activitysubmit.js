
/**
 * 推广营销提交
 * @param fromId 表单ID【必填】
 * @param name 用户姓名【必填】
 * @param phone 电话号码【必填】
 * @param classId 栏目ID【必填】
 * @param okUrl ok页面地址
 * @param isReload 是否刷新页面【true:刷新】
 */
function saveactivity(fromId,mobile,okUrl,isReload) {
	var data =$("#"+fromId).serialize();
	$.ajax({
        type: "POST",
        url: '/Activity/saveActivity.html', //提交给哪个执行
        data: data,
		cache: false,
        dataType: 'json',
        success: function (responseValue) {
        	if (responseValue.errcode==0 ) {
        		$(':input','#'+fromId).not(':button, :submit, :reset,:radio,:hidden').val('').removeAttr('checked');
                $("input[name='houseRemark']").val('');
				alert("恭喜您，申请成功！\n您申请的联系电话为 " + mobile + "，保驾护航网的客服将会在一个工作日之内与您取得联系！");
				if(isReload){
					location.reload(true);
				}
				if (okUrl) {
					if(typeof(okUrl)=='string'){
						window.location.href = okUrl;
					}else if(typeof(okUrl)=='function'){
						okUrl(responseValue);
					}
				}
            }
            else {
                if (responseValue.errmsg == undefined || responseValue.errmsg == "") {
                    alert('提交申请失败，请重新申请或直接联系保驾护航网客服热线：400-175-7315！');
                }
                else {
                    alert(responseValue.errmsg);
                }
                if(isReload){
					location.reload(true);
				}
            }
        },
        error: function (responseValue) {
            alert("恭喜您，申请成功！\n您申请的联系电话为 " + mobile + "，保驾护航网的客服将会在一个工作日之内与您取得联系！");
            if(isReload){
				location.reload(true);
			}
        }
    });
}




/*
*验证是否为“”或者null
*true:是为空；
*/
function isEmpty(str) {
    if (str == null || str == undefined || str == "undefined") {
        return true;
    }
    else {
        if (str.replace(/(^s*)|(s*$)/g, "").length > 0) {
            return false;
        }
        else {
            return true;
        }
    }
}

/*
*验证手机
*true:是手机号码；false:不是手机号码
*/
function isMoblie(mobile) {
    var telzz = /^(13|14|15|17|18)\d{9}$/;
    if (telzz.test(mobile)) {
        return true;
    } else {
        return false;
    }
}

/*
*验证数字
*true:是数字；false:不是数字
*/
function isNumber(num) {
    var telzz = /^\d+$/;
    if (telzz.test(num)) {
        return true;
    } else {
        return false;
    }
}

/*
*验证数字或者包含两位小数
*true:是数字数字包含两位小数；false:不是数字包含两位小数
*\d{1,2}：最少一位小数，最多两位小数
*/
function isDecimal(num) {
    var telzz = /^-?\d+\.?\d{1,2}$/;
    if (telzz.test(num)) {
        return true;
    } else {
        return false;
    }
}
