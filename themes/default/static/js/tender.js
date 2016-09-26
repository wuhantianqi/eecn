
/*
 * tender.js
 * tracy.xu   15/12/14
 * 依赖comon_new.js,jquery 1.9以上
 * }
 */
var _script = document.createElement('script');
var _scriptMd5 = document.createElement('script');
_script.src = "http://static.to8to.com/gb_js/to8torsaszb.js";
_script.type = 'text/javascript';
_scriptMd5.type = 'text/javascript';
_scriptMd5.src = "http://static.to8to.com/gb_js/jQuery.md5.js";
document.getElementsByTagName('head')[0].appendChild(_script);
document.getElementsByTagName('head')[0].appendChild(_scriptMd5);

(function (_$) {
    //招标
    window.tender = function () { 
        /**
         * 招标入口初始化
         * @param options 初始化参数
         * options {
         *   autoPop：0  1 2
         *   autoResult 0 1 2
         *   autoNext  0 走结果块  其他走回调函数
         *   onFirstStepEnd  回调函数
         *   onNextStepEnd   回调
         *   onResultStepEnd  回调
         *   phoneAgain  防止重复提交
         *   rsastatus  启用新版加密
         *   其他字段 自定义 obj 为可能需要提交到服务器的字段名称
         * }
         */
        this.options = {
            autoPop: 0,
            autoResult: 0,
            autoNext: 0,
            onFirstStepEnd: null,
            onNextStepEnd: null,
            onResultStepEnd: null,
            phoneAgain: 0
        };
        //统一招标入口接口
        this.requestURL = "http://to8tozb.to8to.com/zb/zb-index-get.php";
        //提交字段对象集合
        this.sendData = {};
        this.nextSendData = {};
        // 匹配筛选可提交字段
        this.fields = [
            'modeltype', 'checkField', 'tyid', 'toid', 'gid', 'ptag', 'rand',
            'phone', 'rsastr', 'rsastatus', 'rsadata', 'zxxq', 'address', 'qq', 'zbgate', 'yuyue_type', 'square',
            'chenghu', 'shen', 'city', 'town', 'zxys', 'demo', 'upip', 'cityid', 'zxtype', 'zxtype-gz', 'sourcepage',
            'landpage', 'app', 'idfa', 'subpage', 'zxbj_ispass', 'userTime', 'zxtime', 'yuyuemobilemsg', 'to8to_from',
            'to8to_tgid', 'hometype', 'zxtype_two', 'not_send_mobile_msg', 'zxstatus', 'zbymid', 'third_phid', 'fromid',
            'weixinopenid', 'sourceid', 's_sourceid', 'pro_sourceid', 'pro_s_sourceid', 'forum_sourice', 'operating_type',
            'demand_type', 'device_src', 'housetype', 'sendmobiletime', 'invite','respond','refurl','shi','ting','fang',
			'wei','yangtai','chu','brand','zxjc_ptag','zxbj_ptag','submod1','type','dangci','is_bottom_new','t','nowstep','jcFlag',
            'module_source','community','born','zxtime_zxjr','not_send_mobile','huxing','sub_demo_zt','demo_zt','i','jftime','fengge',
            'fitstate','source','verify','id','pos','oldaid','title','demotype','validCode','markCode', 'autoPop','xgtqbj'];
    };

    tender.prototype = {
        // 招标入口
        init: function (options) {
            this.options = _$.extend(this.options, options);
            this.sendData = this.filterFields(this.sendData, this.options);
            if(this.options.modeltype == 1){
                //普通招标
                this.sendZbRequest(this.sendData,this.handleResponse);
            } else if(this.options.modeltype == 2){
                //装修报价
                this.sendZbRequest(this.sendData, this.handleResponseBJ)
            } else if(this.options.modeltype == 3) {
                //预约工地
                this.sendZbRequest(this.sendData, this.handleResponseYY)
            } else if(this.options.modeltype == 4) {
                //装修吉日
                this.sendZbRequest(this.sendData, this.handleResponseJR);
            } else if(this.options.modeltype == 5) {
                //发送效果图
                this.sendZbRequest(this.sendData, this.handleResponseXGT);
            } else if(this.options.modeltype == 8) {
                //新版报价
                this.sendZbRequest(this.sendData, this.handleResponseBJ);
            }
        },
        /*
         *post 请求统一入口函数
         */
        sendZbRequest: function(sendData,func) {
			
			var _this = this, _phone;

            sendData['uuid'] = _this.createGuid();
            _phone = sendData['phone'] || '';
            sendData['enc'] = _$.md5(sendData['uuid'] + _phone);

            _$.ajax({
                type: "GET",
                url: _this.requestURL,
                dataType : "jsonp",//数据类型为jsonp
                jsonpCallback: "jsonpCallback",//服务端用于接收callback调用的function名的参数
                data: sendData,
                beforeSend: function() {
                    if (_this.options.phoneAgain > 0) {
                        return false;
                    } else {
                        _this.options.phoneAgain ++;
                    }
                },
                success: function (res) {
                    func.call(_this, res);
                }
            });

        },

        createGuid: function () {
            for (var a = "", c = 1; 32 >= c; c++) {
                var b = Math.floor(16 * Math.random()).toString(16),
                    a = a + b;
                if (8 == c || 12 == c || 16 == c || 20 == c) a += ""
            }
            return this.guid = a += Math.ceil(1E6 * Math.random())
        },

        /**
         * 返回值处理函数
         * @param res 返回值
         */
        //处理普通招标
        handleResponse: function (res) {
            var self = this;
            if (res.nextstep == 2) {
                //走完善信息模快

                if (res.status == 1) {
                    if (!res.tmpYid) {
                        tender_overFive();
                        self.options.phoneAgain = 0;
                        return;
                    }
                    //判断是否弹窗
                    if (self.options.autoPop == 0) {
                        //走完善信息
                        tender_window_box_close();
						self.perfectInfor(res);
                    } else if(self.options.autoPop == 1){
                        //  直接显示结果
                        self.showResult(res);
                    } else {
                        // 回调函数位置。
                        if (typeof self.options.onFirstStepEnd == 'function') {
							self.options.onFirstStepEnd(res);
                        }
                    }
                    self.options.phoneAgain = 0;
                    return false;
                } else if (res.status == 5) {
                    tender_window_box_close();
                    tender_indexYYFail(res.city);
                    self.options.phoneAgain = 0;
                    return false;
                } else if (res.status == 6){
					tender_codeError();
				} else {
                    var cityname = encodeURI(res.city);
                    var tyid = encodeURI(res.tmpid);

                    if (showPopWin) {
                        showPopWin("http://www.to8to.com/zb/frame_global.php?msg=" + cityname + "&tyid=" + tyid, 456, 254, null, true);
                    } else {
                        _$.getScript('http://static.to8to.com/gb_js/subModal.js?v=1432043909', function () {
                            showPopWin("http://www.to8to.com/zb/frame_global.php?msg=" + cityname + "&tyid=" + tyid, 456, 254, null, true);
                        });
                    }
                    self.options.phoneAgain = 0;
                }
            }
        },
        //处理装修报价函数
        handleResponseBJ: function(res){
            var self = this;
            /*
             * 第一步提交之后的返回数据。
             */
            if(res.nextstep == 2) {
                self.options.onFirstStepEnd(res);
            } else if(res.nextstep == 3) {
                self.options.onResultStepEnd(res);
            }
        },
        //处理预约装修工地
        handleResponseYY: function(res){
            tender_window_box_close();
            if(typeof(res.CODE) != 'undefined' && res.CODE == '1'){
                tender_freeSuceess(res.DATA);
            } else {
                tender_freeFail();
            }
        },
        //处理装修吉日：
        handleResponseJR: function(res){
            var self = this;
            // 侧边调用回调
            if (typeof self.options.onFirstStepEnd == 'function') {
                self.options.onFirstStepEnd(res);
            }
        },
        //处理效果图
        handleResponseXGT: function(res){
            if(res.status==1){
                var self = this;
                tender_setXgtCookie("to8to_mg",this.sendData.phone,7*24*3600*1000, 'xiaoguotu.to8to.com');
                if (typeof self.options.onFirstStepEnd == 'function') {                    
                    self.options.onFirstStepEnd(res);
                } else {
                    tender_window_box_close();
                    var str = '<div class="div_two">\
                                       <div class="two_div" style=" padding-left:50px;">\
                                             <p class="p1">恭喜您发送成功 !</p>\
                                           <p class="p2">短信已发送，土巴兔-互联网装修领导者，感谢您的使用。</p>\
                                       </div>\
                                </div>';
                    _$('.window_box').windowBox({
                        width:480,    //弹框宽度
                        height:220,
                        title: '', //标题
                        wbcStr:str,  //可编辑内容
                        cancleBtn:false,    //是否显示取消按钮
                        confirmBtn:false
                    });
                }
               
            }else if(res.status==0){
                tender_window_box_close();
                var str = '<div class="freeQuote_box_content freeOffer_box_content clear">\
                                 <div class="div_one">\
                                    <div class="one_div">\
                                        <p class="p2">'+res.msg+'</p>\
                                    </div>\
                                </div></div> ';
                _$('.window_box').windowBox({
                    width:480,    //弹框宽度
                    height:150,
                    title:' <p class="p1">发送失败 !</p>',  //标题
                    wbcStr:str,  //可编辑内容
                    cancleBtn:false,    //是否显示取消按钮
                    confirmBtn:false
                });
            }else{
                alert("验证码错误!");
                _$(".freeOffer_box_content").find(':text[name="chkYzm"]').focus();
            }
        },
        /**
         * 过滤初始参数，组装需要发送的数据
         */
        filterFields: function (sendData, options) {
            for (var i in this.fields) {
                if (options[this.fields[i]] == 0 || options[this.fields[i]]) {
                    sendData[this.fields[i]] = options[this.fields[i]];
                }
            }
            //存在电话号码则进行加密处理
            if (sendData.phone && sendData.phone != '' ) {
	            //新版电话加密
	            sendData = this.encryptPhone(sendData);
            };
			return sendData;
        },

        //完善信息数据处理
        perfectInfor: function (res) {
            var _this = this;

            var str = '<form id="subZbForm">\
                        <input type="hidden" id="User_City_1" value="'+res.city+'" name="User_City">\
                        <input type="hidden" value="'+res.tmpYid+'" name="tyid" id="tyid">\
                        <div class="mod_fbbox mod_fbbox_wxserviceV2" style="width:380px;padding-bottom:100px;">\
                            <a href="http://www.to8to.com/help/?id=68" target="_blank" class="help-link">申请服务常见问题&gt;</a>';
                            if (res.homeId) {
                                str += '<div class="fbbox_s1" style="width:308px;margin: 0 auto;">\
                                            <div class="s1_hd">感谢您的申请!</div>\
                                            <div style="text-align: left; color: #f36f20;">'+ '土巴兔将有装修管家与您电话联系，确认您的具体需求。</div>\
                                            <div class="s1_hd_sub" style="width:100%;text-align: left;font-size: 12px;">由于近期申请火爆，一个月内整屋装修开工的业主完善以下信息，我们将优先服务。</div>';
                            } else {
                                str += '<div class="fbbox_s1" style="width:328px;margin: 0 auto;">\
                                            <div class="s1_hd">感谢您的申请!</div>\
                                            <div style="text-align: left;"><span style="color: #f36f20;">本服务由彬讯科技旗下<a style="color: #f36f20;; text-decoration: underline!important; font-weight: bold;" href="http://www.tumanyi.com/company" target="_blank">图满意装修网</a>及具有资质合作的装修公司提供相关服务，图满意将有专属管家与您联系，确认需求！</div>\
                                            <div class="s1_hd_sub" style="width:100%;text-align: left;font-size: 12px;">由于近期申请火爆，一个月内整屋装修开工的业主完善以下信息，我们将优先服务。</div>';
                            };     
                            str += ' </div>\
                            <div class="mod_form" style="width: 308px; margin: 20px auto 0;">';
                            if (res.city == '' || res.city == 0 || typeof(res.city) == 'undefined') {
                                str += '<div class="form_line">' +
                                '<label for="" class="label"><span>*</span>&nbsp;所在省份</label>' +
                                '<div class="element">' +
                                '<div class="clear">' +
                                '<select  class="select select_s" id="User_ShenPop"  name="User_ShenPop" onChange="changeProvince(' + "User_ShenPop" + ',' + "User_CityPop" + ',' + "User_TownPop" + ');"></select>' +
                                '</div>' +
                                '<div class="err_tip"  style="display:none"><span class="ico_error"></span>请选择省份名称</div>' +
                                '</div>' +
                                '</div>';
                                str += '<div class="form_line">' +
                                '<label for="" class="label"><span>*</span>&nbsp;所在城市</label>' +
                                '<div class="element">' +
                                '<div class="clear">' +
                                '<select  class="select select_s" id="User_CityPop" name="User_CityPop" ></select><div style="display:none"><select name="User_TownPop" id="User_TownPop" style="display:none"><option value="">' + '--</option></select></div>' +
                                '</div>' +
                                '<div class="err_tip"  style="display:none"><span class="ico_error"></span>请选择城市名称</div>' +
                                '</div>' +
                                '</div>';
                            } else {
                                str += '<input type="hidden" id="User_Shen_1" value="' + res.shen + '" name="User_Shen"><input type="hidden" id="User_City_1" value="' + res.city + '" name="User_City">';
                                str += '<input type="hidden" id="User_Shen_1" value="' + res.shen + '" name="User_Shen"><input type="hidden" id="User_City_1" value="' + res.city + '" name="User_City">';
                            }
                            if(res.zxtime==''||res.zxtime==0||typeof(res.zxtime)=='undefined'||res.zxtime==null){
                                str += '<div class="form_line">\
                                            <label class="label" for=""><span>*</span>装修时间</label>\
                                            <div class="element">\
                                                <select id="zxtime" name="zxtime" class="select">\
                                                    <option value="">请选择您要装修房屋的时间</option>\
                                                    <option value="半个月内">半个月内</option>\
                                                    <option value="1个月">1个月</option>\
                                                    <option value="2个月">2个月</option>\
                                                    <option value="2个月以上">2个月以上</option>\
                                                </select>\
                                            </div>\
                                        </div>';
                            }
                            if(res.oarea==''||res.oarea==0||typeof(res.oarea)=='undefined'||res.oarea==null){
                                str += '<div class="form_line">\
                                            <label class="label" for=""><span>*</span>装修面积</label>\
                                            <div class="element">\
                                                <div class="text_wrap">\
                                                    <input class="text" type="text" id="square" name="square" >\
                                                    <em class="text_lbl">请输入您要装修房屋的建筑面积</em>\
                                                    <em class="unit">㎡</em>\
                                                </div>\
                                            </div>\
                                        </div>';
                            }    
                            if(res.hometype==''||res.hometype==0||typeof(res.hometype)=='undefined'||res.hometype==null) {
                                str += '<div class="form_line">\
                                    <label class="label" for=""><span>*</span>房屋类型</label>\
                                    <div class="element">\
                                        <select id="hometype" class="select" name="">\
                                            <option value="">请选择您要装修房屋的类型</option>\
                                            <option value="1">新房装修</option>\
                                            <option value="1">旧房翻新</option>\
                                            <option value="3">办公室装修</option>\
                                            <option value="5">店铺装修</option>\
                                            <option value="7">餐厅装修</option>\
                                            <option value="6">酒店装修</option>\
                                            <option value="21">其他类型</option>\
                                        </select>\
                                    </div>\
                                </div>';
                            }
                            if(res.address==''||res.address==0||typeof(res.address)=='undefined'||res.address==null) {
                                str += '<div class="form_line">\
                                            <label class="label" for="">&nbsp;&nbsp;楼盘名称</label>\
                                            <div class="element">\
                                                <div class="text_wrap">\
                                                    <input class="text" type="text" id="zb_areaName" maxlength="24">\
                                                    <em class="text_lbl">请输入您要装修房屋的楼盘名称</em>\
                                                    <div class="loupan_box"></div>\
                                                </div>\
                                            </div>\
                                        </div>';
                            }
                    //完善信息所需字段 暂定为inforType
                    str += '<div class="from_line" style="float: left;width:100%">\
        									<div class="element">\
        										<span class="btn_org mod_fbbox_btn clickstream_tag" style="text-align: center;display:block;width:160px;margin: 0 auto"> 立即提交</span>\
        									</div>\
        								</div>\
        							</div>\
        						</div></form>';
            _$('.window_box').windowBox({
                width: 530,
                title: "",
                wbcStr: str
            });
            if (res.city == '' || res.city == 0 || typeof(res.city) == 'undefined') {
                initGp(function() {
                    var gpmPop = new GlobalProvincesModule();
                    gpmPop.def_province = ["省/市", ""];
                    gpmPop.def_city1 = ["市/地区", ""];
                    gpmPop.initProvince($("User_ShenPop"));
                })
            }

            _$('#square, #zb_areaName').placeholder();
            _$("#zb_areaName").bind('keyup keydown',function(){
                var loupan = _$(this).val();
                if(loupan != 'undefined' && loupan != ''){
                    tender_getLoupan( {'shen':_this.options.shen, 'city':_this.options.city, 'loupan_key': loupan} );
                }    
            });
            _$("#zb_areaName").bind('focus click',function(){
                _$('.loupan_box').hide(); 
            });

            _$('.btn_org').click(function() {
                var chkArr = [{
                    id: '#User_shenPop',
                    info: [{reg: [0], tip: '请选择省份'}]
                },{
                    id: '#user_cityPop',
                    info: [{reg: [0], tip: '请选择城市'}]
                },{
                    id: '#zxtime',
                    info: [{reg: [0], tip: '请选择装修时间'}]
                }, {
                    id: '#square',
                    info: [{reg: [0], tip: '请填写装修面积'}, {reg: [/^[\d\.]+$/], tip: '请填写正确的装修面积'}, {reg: [/^[1-9]{1}\d{0,3}(\.\d{1,2})?$/], tip: '面积不能超过10000，支持两位小数'}]
                }, {
                    id: '#hometype',
                    info: [{reg: [0], tip: '请选择房屋类型'}]
                }];
                //将该验证函数引入
                if(tender_simplifyCheck(chkArr)) {
                    _this.resData = {};
                    if(_$('#User_City_1').val()){
                        _this.resData.city   = _$("#User_City_1").val();
                    } else {
                        _this.resData.shen = _$('#User_ShenPop').val();
                        _this.resData.city = _$('#User_CityPop').val();
                    }
                    _this.resData.square  = _$("#square").val();
                    if (_$("#zb_areaName").val() != '') {
                        _this.resData.address = _$("#zb_areaName").val();
                    };
                    _this.resData.zxtime = _$("#zxtime").val();
                    _this.resData.hometype = _$("#hometype").val();
                    _this.resData.tyid   = _$("#tyid").val();
                    //invite 的含义是？
                    _this.resData.invite = 2;
                    //通过服务器告诉走那个模块
                    _this.resData.modeltype  = res.modeltype;
                    _this.resData.nowstep = res.nextstep;

                    _this.onNextStep(_this.resData);
                }
                // 点击提交后所执行的函数
            });
        },
        onNextStep: function(options){
            this.nextSendData = this.filterFields(this.nextSendData,options);
            this.sendZbRequest(this.nextSendData,this.dealNextStep);
        },
        dealNextStep: function(res){
			var self = this;
            if(res.nextstep == 3) {
                if(self.options.autoNext == 0) {
                    self.showResult(res);
                } else {
                    //回调
                    if (typeof self.onFirstStepEnd == 'function') {
                        self.options.onNextStepEnd(res);
                    }
                }
            }
        },
        // 普通招标结果显示
        showResult: function (res) {
            if (this.options.autoResult == 2) {
                if (typeof this.options.onResultStepEnd == 'function') {
                    this.options.onResultStepEnd(res);
                }
            } else if (this.options.autoResult == 1) {
                str = '恭喜预约成功';
                _$('.window_box').windowBox({
                    width: 530,
                    title: "",
                    wbcStr: str
                });
            } else if (this.options.autoResult == 0) {
                // 根据当前选择流程展示结果
				tender_window_box_close();
                if (res.status == 4) {//显示结果
                    tender_indexYYFail(res.city);
                    return false;
                } else {
                    var weixin_code = "http://img.to8to.com/to8to_img/img_code/code_other.jpg";
                    /*if (tender_getCookie('to8to_townid') == '1130') {
                        //深圳微信个人号
                        weixin_code = 'http://img.to8to.com/to8to_img/img_code/code_1130.jpg';
                    };*/
                    var tip = '微信扫码，获取省钱秘笈';//量房设计报价
                    var str = '<div class="mod_fbbox mod_fbbox_wxserviceV2">\
                                        <div class="fbbox_s1">\
                                            <div class="s1_hd">恭喜您完善资料成功！</div>\
                                            <div class="s1_hd_sub" style="color:#f36f20">' + tip + '</div>\
                                        </div>\
                                        <div class="mod_fbbox_code">\
                                            <div class="mod_fbbox_code_img">\
                                                <img src="' + weixin_code + '" id="weixin_img" width="222" height="222">\
                                            </div>\
                                            <div class="mod_pagetip mod_pagetip_s mod_pagetips_noinfo" id="status_success" style="display:none">\
                                                <span class="mod_pagetip_ico"><em class="ico_tip_ok_s"></em></span>\
                                                <div class="mod_pagetip_bd">\
                                                    <div class="mod_pagetip_title">扫描成功</div>\
                                                </div>\
                                            </div>\
                                            <div class="mod_pagetip mod_pagetip_s mod_pagetips_noinfo" id="status_fail" style="display:none">\
                                                <span class="mod_pagetip_ico" style="padding-left: 50px;"><em class="ico_tip_warn_s"></em></span>\
                                                <div class="mod_pagetip_bd">\
                                                    <div class="mod_pagetip_title">二维码失效</div>\
                                                </div>\
                                            </div>\
                                            <p>您还可以查看更多<a href="http://xiaoguotu.to8to.com/" target="_blank">精品效果图</a><br>或者去<a href="http://mall.to8to.com/" target="_blank">家居商城</a>选购优质建材</p>\
                                        </div>\
                                    </div>';
         //            var tip = '装修名额有限，预付定金即可锁定优先装修资格';//量房设计报价
         //            var str = '<div class="mod_fbbox">\
									// 	<div class="fbbox_s1">\
									// 		<div class="s1_hd">恭喜您完善资料成功！</div>\
									// 		<div class="s1_hd_sub" style="font-size: 16px;padding: 15px 0;line-height: 20px;">' + tip + '</div>\
									// 	</div>\
									// 	<div class="mod_fbbox_code">\
									// 		<p><a href="http://xiaoguotu.to8to.com/" target="_blank" style="color: #f36f20; font-size: 14px;">去预付定金&gt;</a></p>\
									// 	</div>\
									// </div>';
                    _$('.window_box').windowBox({
                        width: 530,
                        title: "",
                        wbcStr: str
                    });
                }
            }

        },
        //电话号码加密
        encryptPhone: function(sendData){
            var reg = /^1{1}[34578]{1}\d{9}$/,
                isPhone = sendData.phone ? reg.test(sendData.phone) : false;

        	//旧版加密 rsastr=1存在
        	if(sendData.rsastr==1){
				sendData.phone = encodeURIComponent(sendData.phone);
				sendData.phoneurlencode = 1;
			} else if (isPhone) {
				//新版加密添加当前标识
                sendData.rsadata = RSAUtilszb.encryptfun(sendData.phone+','+(Math.ceil(Math.random()*10))+','+Math.random())
                sendData.rsadata = encodeURIComponent(sendData.rsadata);
                sendData.rsastatus = 1;
                sendData.phoneurlencode = 1;
                sendData.phone = '';
			}
			return sendData;
        }
    };
    /** Helps function **/

    // 超过5次弹窗
    function tender_overFive() {
        var str = '<span style="float:left; width:100%; height:14px;line-height:14px;margin:20px 0;text-align:center;*padding-bottom:20px">申请次数超过五次</span>';
        _$('.window_box').remove();
        _$('.translucence_layer').remove();
        _$('.window_box').windowBox({
            width: 480,
            title: "提示",
            wbcStr: str
        });
    }
	
	function tender_codeError() {
        var str = '<span style="float:left; width:100%; height:14px;line-height:14px;margin:20px 0;text-align:center;*padding-bottom:20px">验证码错误!</span>';
        _$('.window_box').remove();
        _$('.translucence_layer').remove();
        _$('.window_box').windowBox({
            width: 480,
            title: "提示",
            wbcStr: str
        });
    }

    function tender_indexYYFail(cityname, sem) {
        var failStr = '<div class="apply_fail tender_fail"><span class="as_fail"></span><strong>非常抱歉,您当前的城市' + cityname + '尚未开通装修服务，敬请期待！</strong></div>';
        if (!sem) {
            _$('.window_box').windowBox({
                width: 480,
                height: 200,
                title: "提示",
                wbcStr: failStr,
                closeTime: 6000
            });
        } else {
            _$('.window_box').windowBox({
                width: 480,
                height: 200,
                title: "提示",
                wbcStr: failStr,
                closeFn: 'semYYFailCloseFn'
            });
        }
    }
    //清楚页面弹窗。
    function tender_window_box_close(obj) {
        _$('.window_box').remove();
        _$('.translucence_layer').remove();
        var cb = tender_checkBrowser();
        if (cb.version == "6") {
            _$("html").css("overflow-y", "scroll");
            _$("body").css("overflow-y", "visible");
        } else if (cb.version == "7" && _$('#st_pid').length != 1) {
            _$("html").css("overflow-y", "scroll");
            _$("body").css("overflow-y", "visible");
        } else if (cb.version == "8" && _$('#st_pid').length != 1) {
            _$("html").css("overflow-y", "scroll");
            _$('body').css('overflow-y', 'visible');
        } else {
            _$("body").css("overflow-y", "inherit");
        }
        _$('body').css('margin-right', '0');
        windowBoxOriginHight = 0;
    }
    // 判断浏览器
    function tender_checkBrowser() {
        var u = window.navigator.userAgent.toLocaleLowerCase(),
            msie = /(msie) ([\d.]+)/,
            chrome = /(chrome)\/([\d.]+)/,
            firefox = /(firefox)\/([\d.]+)/,
            safari = /(safari)\/([\d.]+)/,
            opera = /(opera)\/([\d.]+)/,
            ie11 = /(trident)\/([\d.]+)/,
            b = u.match(msie) || u.match(chrome) || u.match(firefox) || u.match(safari) || u.match(opera) || u.match(ie11);
        return {name: b[1], version: parseInt(b[2])};

    }
    // 页面城市插件是否引入判断
    function initGp(callback) {
        if (!window.GlobalProvinces) {
            _$.getScript('http://static.to8to.com/gb_js/GlobalProvinces.js?v=143987836', function() {
                typeof callback === 'function' && callback();
            })
        } else {
            typeof callback === 'function' && callback();
        }
    }
    function tender_simplifyCheck(chkArr) {
        var defaults = {
                info: [],
                parCls: '',//错误加的地方,默认表单元素的父元素
                require: true,//必填,若是需要满足一定条件检测的需传入condition（true:执行检测，false:不执行检测）
                className: 'form_error',//错误提示类名
                labl: 'i',//X的html的标签
                lablClass: 'ico_error',//X的html的标签的类名
                callback: '',
                parentTip: 'form',
                chkType:''
            },
            settings = {},
            a = 0;

        for(var i = 0, len = chkArr.length;i < len;i++) {
            settings = {};
            settings = _$.extend({},defaults,chkArr[i]);
            if(settings.hasOwnProperty('condition')) {
                settings.require = false;
            }
            if(settings.require || (!settings.require && settings.condition)) {//必须，条件成立
                a = _$(settings.id).checkForm2({className:settings.className, info:settings.info, labl:settings.labl, lablClass: settings.lablClass, parCls: settings.parCls, callback: settings.callback, parentTip: settings.parentTip, chkType: settings.chkType});
            }
            // if(!a && settings.condition) {
            //     break;
            // }
            if(!a) {
                break;
            }
        }

        if(a) {
            return true;
        } else {
            return false;
        }

    }
    // 预约申请成功
    function tender_freeSuceess(obj){
        var obj = obj?obj:"24小时";
        var successStr = '<div class="apply_success"><span class="as_true"></span><strong>恭喜您，申请成功!</strong><em>土巴兔客服将于'+obj+'内与您联系！</em></div>';
        jq('.window_box').windowBox({
            width:480,
            height:200,
            title:"提示",
            wbcStr:successStr,
            closeTime:3000
        })
    }
    //预约申请失败
    function tender_freeFail(obj){
        var failStr = '<div class="apply_fail"><span class="as_fail"></span><strong>非常抱歉,您当前的申请失败，请稍候再试！</strong></div>';
        jq('.window_box').windowBox({
            width:480,
            height:257,
            title:"提示",
            wbcStr:failStr,
            closeTime:3000
        })
    }
    //设置cookie值
    function tender_setXgtCookie(name, value, expire) {
        if (!expire){
            expire = 5000;
        }
        var expiry = new Date();
        expiry.setTime(expiry.getTime() + expire);
        document.cookie = name + '=' + value + ';expires=' + expiry.toGMTString() + ';path=/;';
    }
    // form 表单提交数据转成JSON格式
})(jQuery);

(function(_$){
    _$.fn.checkForm2 = function(settings){
        var defaults = {
            calssName:"add_wrong", //报错字符串的class
            content:[], //报错内容数组
            checkType:"text",//检测类型
            labl:'i',//错误提示的标签
            lablClass:'ico_error',//错误提示的标签的类名
            parCls: '',//错误提示所加父元素的标示
            callback: '',//检测成功后执行的函数
            info: [],
            displayNum: true,//单个提示
            parentTip: 'div',//单个提示范围
            blurChk: false,//blur时检测提示错误
            chkType: '',//检测类型，radio单独处理
            callback: false//验证成功后执行的函数，必须是全局变量
        };


        var settings = _$.extend({}, defaults, settings),
            cf = {};
        cf.fn = {};
        if(settings.info.length == 0 ) {
            return false;
        }
        cf.fn.regType = [/\S/];//0:非空字符串

        _$(this).focus(function() {//获取焦点是移除错误
            removeWrongText2(_$(this), settings.parCls, settings);
        });
        if(settings.blurChk){//blur时检测提示错误
            _$(this).blur(function() {
                _check2(cf, settings, _$(this));
            });
        }else if(!settings.blurChk) {//提交时检测提示错误
            var g = _check2(cf, settings, _$(this));
            return g;
        }


        function _check2(cf, settings, blurObj) {
            var strTip = '<div class="'+settings.className+'"><'+settings.labl+' class="'+settings.lablClass+'"></'+settings.labl+'>',//错误提示字符串
                myVal = '',//待验证的value值
                info = '',//验证规则及提示对象
                chkFlag = true,//验证的状态
                reg = [];//验证规则数组
            if(settings.displayNum  && blurObj.parents(settings.parentTip).find('div.'+settings.className+'').length >= 1) {
                return;
            }
            //获取value
            if(settings.checkType =="text") {//text,textarea...
                myVal = blurObj.val();
            } else {//select
                myVal = blurObj.find('option:selected').attr('value');
            }
            //处理radio
            if(settings.chkType == 'radio' && blurObj.filter(':checked').length == 0) {
                strTip += ""+settings.info[0]['tip']+'</div>';
                addWrongText2(blurObj, settings.parCls, strTip, settings.checkType);
                chkFlag = false;
            }

            for(var i = 0; i < settings.info.length; i++){
                info = _$.extend({}, {negate: false}, settings.info[i]);
                reg = [];
                if(!chkFlag) {//验证失败不做以后的验证
                    break;
                }
                if(info.reg && info.tip) {
                    //获取reg
                    for(var j = 0, len = info.reg.length;j < len;j++) {
                        if(typeof info.reg[j] == 'number') {
                            reg.push(cf.fn.regType[info.reg[j]]);
                        } else {
                            reg.push(info.reg[j]);
                        }
                    }

                    //验证
                    for(var k = 0;k < reg.length;k++) {
                        var regRslt = reg[k].test(myVal);
                        if(!regRslt && !info.negate) {//匹配不成功，添加错误提示
                            strTip += ""+info.tip+'</div>';
                            addWrongText2(blurObj, settings.parCls, strTip, settings.checkType);
                            chkFlag = false;
                            break;
                        } else if(regRslt && info.negate) {
                            strTip += ""+info.tip+'</div>';
                            addWrongText2(blurObj, settings.parCls, strTip, settings.checkType);
                            chkFlag = false;
                            break;
                        }
                    }
                }

            }
            if(chkFlag) {
                if(settings.callback) {
                    chkFlag = settings.callback();
                }
                return chkFlag;//成功
            }
        }

        function checkWordLen2(maxLen, range, val) {
            var len = val.length;
            if(!range) {
                if(maxLen < len) {
                    return false;
                }
            } else {
                if(len > range.dmax || len < range.dmin) {
                    return false;
                }
            }

            return true;
        }


        function addWrongText2(obj, cls, str, chkType) {
            if(!cls) { //未传class
                obj.parent().addClass('height_auto');
                obj.parent().append(str);
            } else {
                obj.parents(cls).addClass('height_auto');
                obj.parents(cls).append(str);
            }

            if(chkType != 'select') {
                obj.css('border-color','#ff6767');
            }
        }


        function removeWrongText2(obj, cls, settings) {
            if(!cls) { //未传class
                obj.parent().removeClass('height_auto');
                obj.parent().find('div.'+settings.className+'').remove();
            } else {
                obj.parents(cls).removeClass('height_auto');
                obj.parents(cls).find('div.'+settings.className+'').remove();
            }
            obj.css('border-color','#ccc');
        }

    };
})(jQuery);
function tender_formToJSON (formEle){
    var data = formEle.serializeArray();
    var dataObj = {};
    for(var i in data) {
        dataObj[data[i].name] = data[i].value;
    }
    return dataObj;
}

function showAddressList(){
    // jq("#User_Town_c").on('change',function(){
    //        jq("#user_location").val('');
    //         var shen = jq("#User_Shen_c").val();
    //         var city = jq("#User_City_c").val();
    //         var town = jq("#User_Town_c").val();
    //         getLoupan( {'shen':shen, 'city':city, 'town':town, 'loupan_key': ''} );
        
    // });
    
    
    


}
//根据选中的县地区，展示小区列表
function tender_getLoupan(option){
    if(option.loupan_key == '请输入项目小区名称') option.loupan_key = '';
    jq.get('/api/loupan_search.php?rand='+~(-new Date()/36e5), option, function(data){
        if(data == '0'){
            jq(".loupan_box").hide();
        } else {
            data += '<iframe style="position:absolute;top:0;left:0;width:100%;height:100%;filter:alpha(opacity=0);opacity:0;z-index:-1;"></iframe>';
            jq(".loupan_box").html( data ).slideDown();
            jq(".lbl_in").text('');
            jq(".loupan_box").find("ul li").click(function(){
                jq("#zb_areaName").val( jq(this).text() ).removeClass('height_auto').css('border-color','#d8d8d8').next('em').hide().siblings('.add_wrong').remove();
                jq(".loupan_box").hide();
            });
        }
    });
} 

function tender_getCookie(name, pre) {
    if (pre)
    name = 'to8to_' + name;
    var r = new RegExp("(\\b)" + name + "=([^;]*)(;|$)");
    var m = document.cookie.match(r);
    var res = !m ? "": decodeURIComponent(m[2]);
    var result = stripscript(res);
    return result;

};