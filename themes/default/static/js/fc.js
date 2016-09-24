try{
	var TJBox = {
		'allAllow': true,
		'act': {
			'huafei': {
				'allow': true,
				'css': {
					'margin' : "0 0 0 470px",
					'left' : "50%",
					'top' : "40%"
				},
				'img':"https://www.baifubao.com/content/resource/promo/tuijianUpdate/images/huodong.png"
			},
			'yifen': {
				'allow': true,
				'css': {
					'margin' : "0",
					'left' : "84%",
					'top' : "70%"
				},
				'img':"https://www.baifubao.com/content/resource/promo/tuijianUpdate/images/1baidu.png"
			},
			'zhuanzhang': {
				'allow': true,
				'css': {
					'margin' : "0 0 0 470px",
					'left' : "50%",
					'top' : "40%"
				},
				'img':"https://www.baifubao.com/content/resource/promo/tuijianUpdate/images/huodong.png"
			},
			'huankuan': {
				'allow': true,
				'css': {
					'margin' : "0 0 0 470px",
					'left' : "50%",
					'top' : "40%"
				},
				'img':"https://www.baifubao.com/content/resource/promo/tuijianUpdate/images/huodong.png"
			}
		},
		getAllow: function(name) {
			var that = this;
			return that.act[name].allow;
		},
		setBox: function(imgSrc,css){
			var that = this;
			var img = document.createElement("img");
			img.src = imgSrc;
			
			var a = document.createElement("a");
			a.id = "TJHappyBox";
			a.target = "_blank";
			a.href= "http://qianbao.baidu.com/hd/tj";
			a.appendChild(img);
			
			var body = document.getElementsByTagName("body")[0];
			body.appendChild(a);
			
			a.style.position = "fixed";
			a.style.zIndex = "500";
			a.style.left = css.left;
			a.style.top = css.top;
			a.style.margin = css.margin;
		},
		on: function(node, type, handler) {
			if(node.addEventListener) {
				node.addEventListener(type, handler, false);
			} else if(node.attachEvent) {
				node.attachEvent('on'+ type, handler);
			} else {
				node['on'+type] = handler;
			}
		},
		TJValve: function(name) {
			var that = this;
			this.on(window, 'load', function() {
				if(TJBox.getAllow(name)){
					TJBox.setBox(that.act[name].img,that.act[name].css);
				}
			});
		}

	};
}catch(e){}		