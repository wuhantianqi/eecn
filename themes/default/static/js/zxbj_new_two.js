(function(){
    var bH = jq(document).height(),
        outWrapDiff = bH - jq('#gloWrap').height(),
        browerObj = checkBrowser(),
        ie6 = false,
        bindFlag = false,
        wechatError = false,
        qrcodeData = {},
        qrcodeTimer = 0;

    var zxbj_index = {
        init: function() {

            //初始化客服弹窗
            popCustSrvWin && popCustSrvWin.init();

            initEvent();
        }
    };

    var golbalYYID,
        countDesign = 0,
        countCompany = 0,
        designInfo = [],
        companyInfo = [],        
        repeatFlag = false,  //重复入库标志
        repeatGetMobileYz = true;
    agineRuku = 0;
    workTime = '15分钟';

    var wegitFlag = false;
    if (jq('#windbox').val() == 'boxhref') {
        wegitFlag = true;
    }
    jq('.blo_bd').css('display','none');
    //查看报价明细按钮
    jq('.res_btn_box').on('click','a.res_btn',function(){ 
        if (jq('.res_btn').hasClass('active')) {
            jq('.blo_bd').css('display','block');
            jq(document).scrollTop(1050);
            (typeof clickStream !== 'undefined') && clickStream.getCvParams('1_4_19_424');
        };
    })
    jq('.ele_b').on('click','i',function(){
        jq(this).parent().find('i').removeClass('ele_bt_on');
        jq(this).parent().find('input').attr('value','');
        jq(this).addClass('ele_bt_on');
        jq('#zxtype').attr('value',jq(this).text());
    })
    var squareRemind = null;
    //根据面积显示户型 
    jq('#square').on('keyup', function(){
       
        selectDoorModle(jq(this).val(), this);
    })
    jq('#square').on('keyup', function(){
        if (squareRemind) {
            clearTimeout(squareRemind);
        };
        var square = Number(jq(this).val());
      
        if (square + '' == 'NaN' || jq(this).val() == '' || square >= 30) {
            jq('.text_area').hide();
            return
        };
        squareRemind = setTimeout(function(){
            if (square >= 5 && square< 30) {
               jq('.text_area').show();     
            };
        },300)
    })
    //表单效果
    jq('.text_wrap > input').val("");
    jq('.text_wrap > .text_lbl').click(function () {
        jq(this).parent().find('input').focus().trigger('click');
    });
    jq('.text_wrap > input').on('keydown', function () {
        jq(this).parent().find('.text_lbl').hide();
    });
    jq('.text_wrap > input').blur(function () {
        if (jq(this).val() == '') jq(this).parent().find('.text_lbl').show();
    });
//  jq('div.con_bj_cal').on('click', '#calc_btn', function(){
//      if (validData()) {
//      //提交表单
//      document.getElementById("new_base_info").submit();
//      var mjia = jq('#square').val() * 430;
//      jq('#bprice').html(mjia);
//      jq('.bj_res_t span').html('万元');
//      jq('#materialPay em').html(mjia*0.40);
//      jq('#artificialPay em').html(mjia*0.20);
//      jq('#designPay').html('<em>'+ mjia*0.30 +'元</em>');
//      jq('#qualityPay').html('<em>'+ mjia*0.10 +'元</em>');          
//      }
//  })
    jq('#new_base_info').click(function(){
    	if (validData()) {
        
      	var mjia = jq('#square').val() * 500;
        jq('#bprice').html(mjia*0.8);

        jq('#materialPay em').html(mjia*0.48);
        jq('#artificialPay em').html(mjia*0.32);
        jq('#designPay em').html(0);
        jq('#designPay').append('<del>'+mjia*0.19+'元'+'</del>')
        jq('#qualityPay em').html(0);
        jq('#qualityPay').append('<del>'+mjia*0.11+'元'+'</del>')
        jq('#new_base_info').css("background-position","0 -168px")
        }
    	else{
    		return false;
    	}
    })
    
	
    //数据校验
    function validData(){
        var chkArr = [{
            id: jq('.con_bj_cal .element select[name="shen"]')[0],
            className: 'form_error',
            labl: 'em',
            lablClass: 'ico_error',
            info: [{
                reg: [0],
                tip: '请选择所在地'
            }]
        },{
            id: jq('.con_bj_cal .element select[name="city"]')[0],
            parentTip: '.con_bj_cal ',
            className: 'form_error',
            labl: 'em',
            lablClass: 'ico_error',
            info: [{
                reg: [0],
                tip: '请选择所在地'
            }]
        },{
            id: jq('.con_bj_cal  .text_wrap :input[name="square"]')[0],
            className: 'form_error',
            labl: 'em',
            lablClass: 'ico_error',
            info: [{
                reg:[0],
                tip:'请输入建筑面积'
            },{
                reg:[/^\d+(\.[0-9]?[1-9]{1})?$/],
                tip:'建筑面积不能超过两位小数'
            },{
                reg:[/^[0-4]{1}(\.[0-9]?[1-9]{1})?$/],
                tip:'建筑面积必须大于5', negate:true
            },{
                reg:[/^[1-9]{1}[0-9]{0,2}(\.[0-9]?[1-9]{1})?$|^1000$/],
                tip: '建筑面积必须是1000以内'
            }]
        }];
        var phoneRule = {
            id: jq('.con_bj_cal  .text_wrap :input[name="data[mobile]"]')[0],
            className: 'form_error',
            labl: 'em',
            lablClass: 'ico_error',
            info: [{
                reg: [0],
                tip: '请输入手机号码'
            },{
                reg: [/^1{1}[34578]{1}\d{9}$/],
                tip: '请输入正确的手机号码'
            }]
        };

        if (jq('#phoneInput').length > 0) {
            chkArr.push(phoneRule);
        };
        return simplifyCheck2(chkArr);
    }
    //获取所用数据
    function getTotalDetailInfo(type) {
        if (repeatFlag) {
            return;
        }
        if (wegitFlag)
            type = 'wegitFlag';
        repeatFlag = true;
        var mj = jq('.area_text').val(),
            zflag = wegitFlag ? 1 : 0;

        var data = formToJSON(jq('#new_base_info'));
        data.type = type;
        data.nowstep = 1;

        //需要重新定义一个modeltype
        data.modeltype = 8;
        data.onFirstStepEnd = function(data) {
            //返回数据模拟

            creatDetailBudget(data);

            repeatFlag = false;
        };
        var sendMsg = new tender();
        sendMsg.init(data);

    }
    function formToJSON(formEle) {
        var data = formEle.serializeArray();
        var dataObj = {};
        for (var i in data) {
            dataObj[data[i].name] = data[i].value;
        }
        return dataObj;
    }
    function detailedDisplay(){
        var zxType = jq('.ele_bt_on').text();//房屋类型
        if (zxType == '新房装修') {
            jq('.info_hd>h3>em').text('');
        }else{
            jq('.info_hd>h3>em').text('详细报价清单以新房为准（旧房报价=新房报价+面积*100）');
        }
    }

    function createFreeServiceId() {
        var obj = jq('#zxbj_zxbx').parents('.price_item').find('.item_hd');

        obj.attr('id', 'freeService');
    }

    //生成一个房间的数据
    function creatOneProject(obj, i) {
        var len = obj.detail.length,
            str = '<div class="price_item" onclick="clickStream.getCvParams(\'1_4_7_14\');">';

        if(i == 0) {
            str = '<div class="price_item price_item_unfold" onclick="clickStream.getCvParams(\'1_4_7_14\');">';
        }

        str += '<div class="item_hd"><h3><i class="zxbj_ico_arrow"></i>'+obj.name+'</h3><span class="item_price"><em>'+obj.price+'</em>元</span><em class="item_price_tips">提示：该价格仅为估算价格，精准价格以量房为准</em></div><div class="item_bd"><table><tbody><tr class="price_t"><td class="col_1">空间工程</td><td class="col_2">工程项目</td><td class="col_3">工程量</td><td class="col_4">单价</td><td class="col_5">总价</td><td class="col_6">工艺标准</td></tr>';
        for(var key in obj.detail){
            str += creatOneItem(obj.detail[key]);
        }
        if(obj.tipSrc)
        {
            str += '<tr class="item_price_tip"><td colspan="8">土巴兔质检提醒您：<a href="'+obj.tipSrc+'" target="_blank"><em>'+obj.tip+'</em>[更多]</a></td></tr>';
        }
        str += '</table></tbody></div></div>';

        return str;
    }
    //生成列表的一行
    function creatOneItem(obj) {
        var list = obj.list,
            len = list.length,
            str = '';

        str += '<tr><td class="col_1" rowspan="'+len+'">'+obj.name+'</td><td class="col_2">'+list[0].des+'</td><td class="col_3"><span>'+list[0].num+'</span></td><td class="col_4"><span>'+Math.floor(list[0].unitprice)+'</span></td><td class="col_5"><span>'+list[0].total+'</span></td><td class="col_6">'+list[0].note+'</td></tr>';

        for(var i = 1; i < len; i++) {
            str += '<tr><td class="col_2">'+list[i].des+'</td><td class="col_3"><span>'+list[i].num+'</span></td><td class="col_4"><span>'+Math.floor(list[i].unitprice)+'</span></td><td class="col_5"><span>'+list[i].total+'</span></td><td class="col_6">'+list[i].note+'</td></tr>';
        }

        return str;
    }
    //生成详细装修预算表
    //var priceInfo = [{name: '客厅工程1', price: 1111, tipSrc:'http://www.taobao.com', tip:'1整体橱柜装修看好这六点可远离陷阱...', detail: [{name: '墙面1', list: [{des: '铲除墙面腻子层', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）'}, {des: '墙面手刷乳胶漆（多乐士金装五合一，一底两面）', num: 200, unitprice: 2, total: 4, note: '多乐士金装五合一（一底两面）,滚筒,砂皮,刷把等'}]}, {name: '墙面2', list: [{des: '铲除墙面腻子层2', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）2'}]}]}, {name: '客厅工程2', price: 1111, tipSrc:'http://www.taobao.com', tip:'2整体橱柜装修看好这六点可远离陷阱...', detail: [{name: '墙面1', list: [{des: '铲除墙面腻子层', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）'}, {des: '墙面手刷乳胶漆（多乐士金装五合一，一底两面）', num: 200, unitprice: 2, total: 4, note: '多乐士金装五合一（一底两面）,滚筒,砂皮,刷把等'}]}, {name: '墙面2', list: [{des: '铲除墙面腻子层2', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）2'}]}]}, {name: '客厅工程3', price: 1111, tipSrc:'http://www.taobao.com', tip:'3整体橱柜装修看好这六点可远离陷阱...', detail: [{name: '墙面1', list: [{des: '铲除墙面腻子层', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）'}, {des: '墙面手刷乳胶漆（多乐士金装五合一，一底两面）', num: 200, unitprice: 2, total: 4, note: '多乐士金装五合一（一底两面）,滚筒,砂皮,刷把等'}]}, {name: '墙面2', list: [{des: '铲除墙面腻子层2', num: 100, unitprice: 1, total: 3, note: '铲除墙面腻子层（铲到红砖另计）2'}]}]}];
    function creatDetailBudget(data) {
        var total_price = (data.to8to_totle_price/10000).toFixed(1); 

        jq('.calc-btn').addClass('recalc');
        jq('.bj_explain').show();
        jq('input[name="phone"]').val('');
        jq('#phoneInput').remove();
        jq('#bprice').html(total_price);
        jq('.bj_res_t span').html('万元');
        jq('#materialPay em').html(data.to8to_cl_price);
        jq('#artificialPay em').html(data.to8to_rg_price);
        jq('#designPay').html('<em>0</em>元<del class="to8to_zj">'+ data.normal_sj_price +'元</del>');
        jq('#qualityPay').html('<em>0</em>元<del class="to8to_zj">'+ data.normal_zj_price +'元</del>');
    }

    function priceInDOM(data,ele,homeMsg){
        var shi = 0;
        var ting = 0;
        var chu = 0;
        var wei = 0;
        var yang = 0;
        var qita = 0;
        for(var i = 0; i< homeMsg.length; i++) {
            if(homeMsg[i].key=='shi_arr[]')
            {
                shi += data[i].price;
            }

            if(homeMsg[i].key=='ting_arr[]')
            {
                ting += data[i].price;
            }
            if(homeMsg[i].key=='chu_arr[]')
            {
                chu += data[i].price;
            }
            if(homeMsg[i].key=='wei_arr[]')
            {
                wei += data[i].price;
            }
            if(homeMsg[i].key=='yangtai_arr[]')
            {
                yang += data[i].price;
            }

        }
        jq('#bedroomPay').html(shi + '<em>元</em>');
        jq('#liveroomPay').html(ting+ '<em>元</em>');
        jq('#kitchenPay').html(chu+ '<em>元</em>');
        jq('#washroomPay').html(wei+ '<em>元</em>');
        jq('#balconyPay').html(yang+ '<em>元</em>');
        ele.eq(5).find('strong').html(data[homeMsg.length].price + '<em>元</em>');
    }


    //根据面积显示户型 
    function selectDoorModle(square, squareEle){
        var square = Number(square);
        if (square + '' == 'NaN' || jq(squareEle).val() == '') {
            return
        };
        if (square < 60) {
            jq('#shi').val(1);
            jq('#ting').val(1);
            jq('#chu').val(1);
            jq('#wei').val(1);
            jq('#yangtai').val(1);
        } else if (square >= 60 && square < 90) {
            jq('#shi').val(2);
            jq('#ting').val(1);
            jq('#chu').val(1);
            jq('#wei').val(1);
            jq('#yangtai').val(1);
        } else if ( square >= 90 && square < 150) {
            jq('#shi').val(3);
            jq('#ting').val(2);
            jq('#chu').val(1);
            jq('#wei').val(2);
            jq('#yangtai').val(1);
        }
        else if (square >= 150) {
            jq('#shi').val(4);
            jq('#ting').val(2);
            jq('#chu').val(1);
            jq('#wei').val(2);
            jq('#yangtai').val(2);
        }
    }
})(jQuery)