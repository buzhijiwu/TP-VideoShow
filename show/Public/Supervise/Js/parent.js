/**
 * Created by dell on 2016/7/1.
 */
//左侧列表操作
var leftMenu = {
    _lis : $(".supervise-menu .list li"),


    menuClick : function () {
        this._lis.on("click", function () {
            $(this).addClass("active").siblings().removeClass("active");
            $("#iframe").attr("src",$(this).attr("data-src"));
        })
    }

};

leftMenu.menuClick();
