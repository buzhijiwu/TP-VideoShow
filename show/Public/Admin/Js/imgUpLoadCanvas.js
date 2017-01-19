
(function () {
    var file = document.getElementById("file");             //上传的图片input
    var canvas = document.getElementById("mianCanvas");         //图片的canvas
    var canvas1 = document.getElementById("shadeCanvas");       //截图区域canvas
    var canvas2 = document.getElementById("previewCanvas");       //预览区域canvas
    var img = document.getElementById("hiddenImg");               //截图区域的图片
    var rotateBtn = document.getElementById("rotate");      //翻转按钮
    var submitBtn = document.getElementById("submit");      //提交按钮
    var form = document.getElementById("imgForm");             //form
    var context = canvas.getContext("2d");
    var context1 = canvas1.getContext("2d");
    var context2 = canvas2.getContext("2d");
    var imgWidth = 0;                                       //截图区域的图片默认宽度
    var imgHeight = 0;                                      //截图区域的图片默认高度
    var mouseLeft = 0;                                      //鼠标距离屏幕左边的距离
    var mouseTop = 0;                                       //鼠标距离屏幕上边的距离
    var inMouseLeft = 0;                                    //鼠标在框内距左边的距离
    var inMouseTop = 0;                                     //鼠标在框内距上边的距离
    var lastLeft = 0;                                       //开始移动的时候，框的左边坐标
    var lastTop = 0;                                        //开始移动的时候，框的上边坐标
    var canvasTop = 0;                                      //第一个canvas的显示上坐标
    var canvasLeft = 0;                                     //第一个canvas的显示左坐标
    var canvasWidth = 0;                                    //第一个canvas的宽度
    var canvas1Width = 0;                                    //第二个canvas的宽度
    var canvasHeight = 0;                                   //第一个canvas的高度
    var canvas1Height = 0;                                   //第二个canvas的高度
    var moveLeft = 0;
    var moveTop = 0;
    var scale = 1;
    var scaleWidth = 50;
    var afterScaleWidth = 50;
    var rotateNum = 0;
    var overLeft = 0;
    var overTop = 0;
    var minScaleWidth = 0;
    var maxLastWidth = 0;
    var defaultLeft = $(".canvas-wrap").offset().left;
    var defaultTop = $(".canvas-wrap").offset().top;

    //默认显示已有图片
    img.src = document.getElementById("bigpic").value;

    img.onload = function () {
        context2.drawImage(img,0,0,img.width,img.height,0,0,150,150);
    };

    var reader = new FileReader();                          //读取file文件

    //文件改变时，触发预览的参数
    file.onchange = function () {
        imgWidth = 0;
        imgHeight = 0;
        mouseLeft = 0;
        mouseTop = 0;
        inMouseLeft = 0;
        inMouseTop = 0;
        lastLeft = 0;
        lastTop = 0;
        canvasTop = 0;
        canvasLeft = 0;
        canvasWidth = 0;
        canvasHeight = 0;
        moveLeft = 0;
        moveTop = 0;
        scale = 1;
        rotateNum = 0;
        canvas2.style.transform = "rotate(0deg)";

        reader.readAsDataURL(file.files[0]);

        reader.onload = function (ev) {
            lastLeft = 0;
            lastTop = 0;

            canvas.style.backgroundColor = "#000";

            canvas1.onmousemove = move(ev);
            canvas1.onmousedown = null;
            document.documentElement.onmouseup = null;
            scaleWidth = 50;
            afterScaleWidth = 50;

            img.src = this.result;

            img.onload = function () {
                imgWidth = img.width;
                imgHeight = img.height;

                if(imgWidth > 400 && imgHeight >= 400){

                    if(imgWidth > imgHeight){
                        overTop = canvasTop = parseInt((400-400/imgWidth*imgHeight)/2);
                        overLeft = canvasLeft = 0;
                        canvasWidth = 400;
                        minScaleWidth = canvasHeight = parseInt(400/imgWidth*imgHeight);
                        scale = 400/imgWidth;

                        }else{
                            overTop = canvasTop = 0;
                            overLeft = canvasLeft = parseInt((400-400/imgHeight*imgWidth)/2);
                            minScaleWidth = canvasWidth = parseInt(400/imgHeight*imgWidth);
                            canvasHeight = 400;
                            scale = 400/imgHeight;
                        }

                    canvas1Width = canvas1.width =  canvas.width =canvasWidth;
                    canvas1Height = canvas1.height = canvas.height = canvasHeight;

                    canvas1.style.marginTop = canvas.style.marginTop = canvasTop+"px";
                    canvas1.style.marginLeft = canvas.style.marginLeft = canvasLeft+"px";

                    lastLeft = 0;
                    lastTop = 0;

                    //清空画布、画出图片、闭合路径
                    context.clearRect(0,0,400,400);
                    context.drawImage(img,0,0,imgWidth,imgHeight,0,0,canvasWidth,canvasHeight);
                    context.closePath();

                    drawScale(0,0,50,canvasWidth,canvasHeight);

                    canvas1.onmousemove = move();
                }else{
                    alert(validate.mistake48);
                    form.reset();
                    context.clearRect(0,0,400,400);
                    context1.clearRect(0,0,400,400);
                    context2.clearRect(0,0,150,150);

                    canvas.style.backgroundColor = "#fff";

                    canvas1.width =  canvas.width =0;
                    canvas1.height = canvas.height = 0;

                    //默认显示已有图片
                    img.src = document.getElementById("bigpic").value;

                    img.onload = function () {
                        context2.drawImage(img,0,0,img.width,img.height,0,0,150,150);
                    };

                }

            }

        };

    };

    function drawScale(canvasLeft ,canvasTop, scaleWidth,canvasWidth,canvasHeight){

        //清空画布、画出半透明的框、闭合路径
        context1.clearRect(0,0,400,400);
        context1.beginPath();
        context1.fillStyle="rgba(255,255,255,0.7)";
        context1.fillRect(0,0,canvasWidth,canvasHeight);
        context1.closePath();

        //设置目标与源文件交叉区域的效果为透明、开始路径、画框（宽150，高150）、填充、将交叉区域效果改为默认
        context1.globalCompositeOperation="destination-out";
        context1.beginPath();
        context1.fillStyle = "red";
        context1.rect(canvasLeft,canvasTop,scaleWidth,scaleWidth);
        context1.fill();
        context1.globalCompositeOperation="source-over";
        context1.closePath();

        context1.beginPath();
        context1.strokeStyle = "#444";
        context1.fillStyle = "#fff";
        context1.arc(scaleWidth + canvasLeft,scaleWidth + canvasTop,5,0,2*Math.PI,true);
        context1.stroke();
        context1.fill();
        context1.closePath();

        //将预览的canvas转换为地址
        context2.clearRect(0,0,150,150);

        switch (rotateNum){
            case 0:
                context2.drawImage(img,canvasLeft/scale,canvasTop/scale,scaleWidth/scale,scaleWidth/scale,0,0,150,150);
                break;
            case 1:
                context2.drawImage(img,canvasTop/scale,Math.ceil(canvasWidth-scaleWidth-canvasLeft)/scale,scaleWidth/scale,scaleWidth/scale,0,0,150,150);
                break;
            case 2:
                context2.drawImage(img,Math.ceil(canvasWidth-scaleWidth-canvasLeft)/scale,Math.ceil(canvasHeight-scaleWidth-canvasTop)/scale,scaleWidth/scale,scaleWidth/scale,0,0,150,150);
                break;
            case 3:
                context2.drawImage(img,Math.ceil(canvasHeight-scaleWidth-canvasTop)/scale,canvasLeft/scale,scaleWidth/scale,scaleWidth/scale,0,0,150,150);
                break;
        }


    }

    function move () {
        return function (ev) {
            canvasTop = canvas1.offsetTop-1;
            canvasLeft = canvas1.offsetLeft-46;

            mouseLeft = ev.pageX;
            mouseTop = ev.pageY;

            inMouseLeft = parseInt(mouseLeft - canvasLeft - defaultLeft);
            inMouseTop = parseInt(mouseTop - canvasTop - defaultTop);

            if(inMouseLeft >= lastLeft  && inMouseLeft <= parseInt(lastLeft + afterScaleWidth - 3)  && inMouseTop >= lastTop  && inMouseTop <= parseInt(lastTop + afterScaleWidth - 3)  ){
                canvas1.style.cursor = "move";

                canvas1.onmousedown = function (event) {
                    var x = event.pageX;
                    var y = event.pageY;

                    canvas1.onmousemove = null;

                    canvas1.onmousemove= function (eve) {

                        var x1 = eve.pageX;
                        var y1 = eve.pageY;

                        moveLeft = parseInt(x1 - x + lastLeft);
                        moveTop = parseInt(y1 - y + lastTop);

                        if(rotateNum%2 == 1){
                            if(moveTop <= 0 ){
                                moveTop = 0;
                            }else if( moveTop >= parseInt(canvasWidth - afterScaleWidth)){
                                moveTop = parseInt(canvasWidth - afterScaleWidth);
                            }else{
                                moveTop = parseInt(y1 - y + lastTop);
                            }
                            if( moveLeft <= 0){
                                moveLeft = 0;
                            }else if( moveLeft >= parseInt(canvasHeight - afterScaleWidth)){
                                moveLeft = parseInt(canvasHeight - afterScaleWidth);
                            }else{
                                moveLeft = parseInt(x1 - x + lastLeft);
                            }

                        }else{
                            if(moveLeft <= 0 ){
                                moveLeft = 0;
                            }else if( moveLeft >= parseInt(canvasWidth - afterScaleWidth)){
                                moveLeft = parseInt(canvasWidth - afterScaleWidth);
                            }else{
                                moveLeft = parseInt(x1 - x + lastLeft);
                            }

                            if( moveTop <= 0){
                                moveTop = 0;
                            }else if( moveTop >= parseInt(canvasHeight - afterScaleWidth)){
                                moveTop = parseInt(canvasHeight - afterScaleWidth);
                            }else{
                                moveTop = parseInt(y1 - y + lastTop);
                            }
                        }

                        drawScale(moveLeft , moveTop , afterScaleWidth , canvas1Width , canvas1Height);

                    };

                    document.documentElement.onmouseup = function () {

                        lastLeft = moveLeft;
                        lastTop = moveTop;

                        canvas1.onmousemove = move(ev);
                        canvas1.onmousedown = null;
                        document.documentElement.onmouseup = null;

                    }

                }

            }else if(inMouseLeft > parseInt(lastLeft + afterScaleWidth - 5)  && inMouseLeft < parseInt(lastLeft + afterScaleWidth + 5)  && inMouseTop > parseInt(lastTop + afterScaleWidth - 5)  && inMouseTop < parseInt(lastTop + afterScaleWidth + 5)  ){
                canvas1.style.cursor="nw-resize";

                canvas1.onmousedown = function (event) {
                    var x = event.pageX;
                    var y = event.pageY;

                    canvas1.onmousemove = null;

                    document.documentElement.onmousemove= function (eve) {

                        var x1 = eve.pageX;
                        var y1 = eve.pageY;

                        scaleWidth = parseInt(x1 - x + afterScaleWidth);


                        if(rotateNum%2 == 1){
                            maxLastWidth =canvasHeight-lastLeft < canvasWidth-lastTop ? canvasHeight-lastLeft : canvasWidth-lastTop ;

                            if(scaleWidth <= 50 ){
                                scaleWidth = 50;
                            }else if( scaleWidth > maxLastWidth ){
                                scaleWidth = maxLastWidth;
                            }
                        }else{
                            maxLastWidth = canvasWidth-lastLeft < canvasHeight-lastTop ? canvasWidth-lastLeft : canvasHeight-lastTop ;

                            if(scaleWidth <= 50 ){
                                scaleWidth = 50;
                            }else if( scaleWidth > maxLastWidth ){
                                scaleWidth = maxLastWidth;
                            }
                        }



                        if(scaleWidth <= 50 ){
                            scaleWidth = 50;
                        }else if( scaleWidth > maxLastWidth ){
                            scaleWidth = maxLastWidth;
                        }

                        drawScale(lastLeft , lastTop , scaleWidth , canvas1Width , canvas1Height);

                    }

                };
                document.documentElement.onmouseup = function () {

                    afterScaleWidth = scaleWidth;

                    canvas1.onmousemove = move(ev);
                    canvas1.onmousedown = null;
                    document.documentElement.onmouseup = null;
                    document.documentElement.onmousemove = null;

                }
            }else{
                canvas1.style.cursor = "default";
                canvas1.onmousedown = null;
            }
        }

    }

    rotateBtn.onclick = function (ev) {
        rotateNum ++;
        rotateNum = rotateNum > 3 ? 0 : rotateNum;

        if(imgWidth > imgHeight){
            overTop = canvasTop = parseInt((400-400/imgWidth*imgHeight)/2);
            overLeft = canvasLeft = 0;
            canvasWidth = 400;
            minScaleWidth = canvasHeight = parseInt(400/imgWidth*imgHeight);
            scale = 400/imgWidth;

            context.clearRect(0,0,400,400);
            switch (rotateNum){

                case 0:
                    canvas1.width =  canvas.width = canvasWidth;
                    canvas1.height = canvas.height = canvasHeight;
                    context.translate(0,0);

                    canvas1.style.marginLeft =canvas.style.marginLeft = 0;
                    canvas1.style.marginTop = canvas.style.marginTop = canvasTop+"px";

                    canvas1Width = canvasWidth;
                    canvas1Height = canvasHeight;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";
                    break;
                case 1:
                    canvas1.width = canvas.width = canvasHeight;
                    canvas1.height =canvas.height = canvasWidth ;
                    context.translate(canvasHeight,0);

                    canvas1.style.marginLeft =canvas.style.marginLeft = canvasTop+"px";
                    canvas1.style.marginTop = canvas.style.marginTop = 0;

                    canvas1Width = canvasHeight;
                    canvas1Height = canvasWidth;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
                case 2:
                    canvas1.width = canvas.width = canvasWidth;
                    canvas1.height =canvas.height = canvasHeight;
                    context.translate(canvasWidth,canvasHeight);

                    canvas1.style.marginLeft =canvas.style.marginLeft = 0;
                    canvas1.style.marginTop = canvas.style.marginTop = canvasTop+"px";

                    canvas1Width = canvasWidth;
                    canvas1Height = canvasHeight;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
                case 3:
                    canvas1.width = canvas.width = canvasHeight;
                    canvas1.height =canvas.height = canvasWidth ;
                    context.translate(0,canvasWidth);

                    canvas1.style.marginLeft =canvas.style.marginLeft = canvasTop+"px";
                    canvas1.style.marginTop = canvas.style.marginTop = 0;

                    canvas1Width = canvasHeight;
                    canvas1Height = canvasWidth;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
            }

        }else{
            overTop = canvasTop = 0;
            overLeft = canvasLeft = parseInt((400-400/imgHeight*imgWidth)/2);
            minScaleWidth = canvasWidth = parseInt(400/imgHeight*imgWidth);
            canvasHeight = 400;
            scale = 400/imgHeight;

            context.clearRect(0,0,400,400);
            switch (rotateNum){

                case 0:
                    canvas1.width =  canvas.width = canvasWidth;
                    canvas1.height = canvas.height = canvasHeight;
                    context.translate(0,0);

                    canvas1.style.marginLeft =canvas.style.marginLeft =  canvasLeft+"px";
                    canvas1.style.marginTop = canvas.style.marginTop = 0 ;

                    canvas1Width = canvasWidth;
                    canvas1Height = canvasHeight;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
                case 1:
                    canvas1.width = canvas.width = canvasHeight;
                    canvas1.height =canvas.height = canvasWidth ;
                    context.translate(canvasHeight,0);

                    canvas1.style.marginLeft =canvas.style.marginLeft = 0;
                    canvas1.style.marginTop = canvas.style.marginTop = canvasLeft+"px";

                    canvas1Width = canvasHeight;
                    canvas1Height = canvasWidth;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
                case 2:
                    canvas1.width = canvas.width = canvasWidth;
                    canvas1.height =canvas.height = canvasHeight;
                    context.translate(canvasWidth,canvasHeight);

                    canvas1.style.marginLeft =canvas.style.marginLeft =  canvasLeft+"px";
                    canvas1.style.marginTop = canvas.style.marginTop = 0 ;

                    canvas1Width = canvasWidth;
                    canvas1Height = canvasHeight;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
                case 3:
                    canvas1.width = canvas.width = canvasHeight;
                    canvas1.height =canvas.height = canvasWidth ;
                    context.translate(0,canvasWidth);

                    canvas1.style.marginLeft = canvas.style.marginLeft = 0;
                    canvas1.style.marginTop = canvas.style.marginTop = canvasLeft+"px";

                    canvas1Width = canvasHeight;
                    canvas1Height = canvasWidth;
                    canvas2.style.transform = "rotate("+90*rotateNum+"deg)";

                    break;
            }
        }


            if(rotateNum%2 == 1){
                if(moveTop <= 0 ){
                    moveTop = 0;
                }else if( moveTop >= canvasWidth - afterScaleWidth){
                    moveTop = canvasWidth - afterScaleWidth;
                }
                if( moveLeft <= 0){
                    moveLeft = 0;
                }else if( moveLeft >= canvasHeight - afterScaleWidth){
                    moveLeft = canvasHeight - afterScaleWidth;
                }
            }else{
                if(moveLeft <= 0 ){
                    moveLeft = 0;
                }else if( moveLeft >= canvasWidth - afterScaleWidth){
                    moveLeft = canvasWidth - afterScaleWidth;
                }
                if( moveTop <= 0){
                    moveTop = 0;
                }else if( moveTop >= canvasHeight - afterScaleWidth){
                    moveTop = canvasHeight - afterScaleWidth;
                }
            }

        lastLeft = moveLeft;
        lastTop = moveTop;

        context.rotate(Math.PI/2*rotateNum);

        context.drawImage(img,0,0,imgWidth,imgHeight,0,0,canvasWidth,canvasHeight);

        drawScale(moveLeft , moveTop , scaleWidth , canvas1Width , canvas1Height);


    };
    submitBtn.onclick = function (e) {
        e.preventDefault();

        var formD = new FormData(form);

        formD.append("x",lastLeft/scale);
        formD.append("y",lastTop/scale);
        formD.append("width",scaleWidth/scale);
        formD.append("height",scaleWidth/scale);
        formD.append("scale",scale);
        formD.append("rotate",-90*rotateNum);
        formD.append("userid",$("#userid").val());

        var oReq = new XMLHttpRequest();

        oReq.onreadystatechange = function () {
            if (oReq.readyState==4 && oReq.status==200) {
                alert(JSON.parse(oReq.responseText).msg);
                window.location.href = '/Admin/Show/user_edit/id/'+$("#userid").val()
            }
        };

        oReq.open("POST", "/Admin/Show/updateBigheadpic",true);

        oReq.send(formD);


    };

})();