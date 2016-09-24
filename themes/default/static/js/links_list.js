try {
    (function() {
        // 编辑区开始-------------------------------------------------------------------------------
        var CONFIG = {
            'link1': {
                'img': 'https://www.baifubao.com/content/resource/1baidu/links/ticket.jpg',
                'url': 'http://1.baidu.com/product/detail?product_id=d5ef82a0b62f56663ed529b5&preview=1'
            },
            'link2': {
                'img': 'https://www.baifubao.com/content/resource/1baidu/links/recommend.jpg',
                'url': 'http://qianbao.baidu.com/hd/tj?1'
            }
        };
        // 编辑区结束-------------------------------------------------------------------------------
        var $E = function(id) {
            return document.getElementById(id);
        };
        var _html = [
            '<ul>',
            '<li style="width:495px;"><a href="' + CONFIG.link1.url + '" target="_blank"><img width="495" height="105" src="' + CONFIG.link1.img + '" /></a></li>',
            '<li style="width:495px;" class="last"><a href="' + CONFIG.link2.url + '" target="_blank"><img width="495" height="105" src="' + CONFIG.link2.img + '" /></a></li>',
            '</ul>'
        ].join('');
        $E('links_list').innerHTML = _html;
    })();
} catch (e) {}