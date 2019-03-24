//内容容器
let main_Container = document.getElementById("zm_bangumi_content");
//分页容器
let nav_Container = document.getElementById("zm_bangumi_nav");

let bangumiData = null;
let bangumiItemData = null;

let configData = {
    pageNum: 0,
    pageNow: 0,
    //每页显示的最多Item数量
    singleItemNum: 0,
    //每页显示的最多的NAV数量
    singleNavNum: 5,
    mainColor: "#ff8c83"
}

function parseBangumiData(args) {
    if(!main_Container || !nav_Container)
    {
        main_Container = document.getElementById("zm_bangumi_content");
        nav_Container = document.getElementById("zm_bangumi_nav");
    }
    if (args.messageType === "zm_bangumi_data") {
        bangumiData = args;
        bangumiItemData = args.messageContent.content;
        if(args.messageCode != 200 || !args.messageContent.content || !args.messageContent.content[0]){
            console.log(args);
            main_Container.innerHTML = null;
            nav_Container.innerHTML = null;
            main_Container.innerText = args.messageContent.content;
            return;
        }
        //TODO 生成分页信息与当前页内容
        if (!bangumiItemData || bangumiItemData === "") {
            //TODO 提示没有追番信息
            return;
        }
        configData.singleItemNum = (bangumiData.messageContent.singleItemNum <= 0 ? 10 : bangumiData.messageContent.singleItemNum);
        configData.singleNavNum = (bangumiData.messageContent.singleNavNum <= 0 ? 3 : bangumiData.messageContent.singleNavNum);
        if(configData.singleNavNum % 2 == 0){
            configData.singleNavNum++;
        }
        configData.mainColor = bangumiData.messageContent.mainColor;
        //总分页数
        configData.pageNum = Math.ceil(bangumiItemData.length / configData.singleItemNum);
        //当前分页
        configData.pageNow = 1;
        JumpPage(configData.pageNow);
    } else {
        //TODO 获取错误
        alert("获取追番信息错误!");
    }
}

function onBtnClick(obj) {
    //TODO 跳转并显示对应分页
}

function JumpPage(index) {

    if (index < 1 || index > configData.pageNum) {
        main_Container.innerHTML = "此地禁止使用魔法!";
        return;
    }
    if (configData.pageNum == 0) {
        main_Container.innerHTML = "OOPS!追番数据消失了!";
        return;
    }

    //设置主颜色
    if(document.body.style.getPropertyValue("--zm_bangumi_color") != configData.mainColor){
        document.body.style.setProperty('--zm_bangumi_color', configData.mainColor);
    }
    //生成当前的页面
    refreshItems(index);
    //刷新导航栏
    refreshNav(index);
}

function refreshItems(index){
    if (index != configData.pageNow) {
        return;
    }
    main_Container.innerHTML = null;
    //根据当前页拿到所需的Item
    let itemOrigin = (index -1) * configData.singleItemNum;

    for(let i = 0; i <configData.singleItemNum;i++){
        if(itemOrigin >= bangumiItemData.length){
            return;
        }
        let obj = bangumiItemData[itemOrigin];
        let itemDom = getItemDom(obj);
        if(itemDom){
            main_Container.appendChild(itemDom);
        }
        itemOrigin++;
    }
}

function refreshNav(index) {
    if (index != configData.pageNow) {
        return;
    }
    nav_Container.innerHTML = "";
    //"上一页"
    let prevBtn = getBtnDom("上一页", "上一页",true,prevPage);
    nav_Container.appendChild(prevBtn);

    let centerNavIndex = Math.ceil(configData.singleNavNum / 2);

    if (configData.pageNow < centerNavIndex + 1) {
        //贴最左
        //console.log("最左")
        for (let i = 1; i <= configData.pageNum; i++) {
            let newBtn = null;
            if (i < configData.pageNow) {
                //前
                newBtn = getBtnDom(i, i);
            } else if (i == configData.pageNow) {
                newBtn = getBtnDom(i, i, false);
                newBtn.className = "current";
            } else if (i > configData.pageNow && i <= configData.singleNavNum) {
                //后固定
                newBtn = getBtnDom(i, i);
                //最后的判断条件，避免出现1，2，3……4的情况
            } else if (i > configData.singleNavNum && configData.singleNavNum < configData.pageNum) {
                //超出
                newBtn = getBtnDom("…", "…", false);
                newBtn.className = "none";
            }
            if(newBtn){
                nav_Container.appendChild(newBtn);
            }
            if (i > configData.singleNavNum) {
                //后边的无需生成
                break;
            }
        }
        if(configData.pageNum > configData.singleNavNum)
        {
            //最后一页
            let lastButton = getBtnDom(configData.pageNum, configData.pageNum);
            nav_Container.appendChild(lastButton);
        }
        
    }else if(configData.pageNow < configData.pageNum - centerNavIndex){
        //靠中间
        //console.log("中间");
        //第一页
        let startBtn = getBtnDom(1, 1);
        nav_Container.appendChild(startBtn);

        for(let i = 1; i <= configData.pageNum;i++){
            //console.log("ss" + i);
            let newBtn = null;
            if(i <= configData.pageNow - centerNavIndex){
                if(i == 1){
                    newBtn = getBtnDom("…","…",false);
                    newBtn.className = "none";
                }else{
                    //跳过无用的循环
                    i = configData.pageNow - centerNavIndex;
                    continue;
                }
            }else if(i == configData.pageNow){
                newBtn = getBtnDom(i,i,false);
                newBtn.className = "current";
            }else if(i < configData.pageNow || i < configData.pageNow + centerNavIndex){
                //在它附近的情况
                newBtn = getBtnDom(i,i,true);
            }else if(i >= configData.pageNow + centerNavIndex && configData.singleNavNum < configData.pageNum){
                newBtn = getBtnDom("…","…",false);
                newBtn.className = "none";
            }
            if(newBtn){
                nav_Container.appendChild(newBtn);
            }
            if(i >= configData.pageNow + centerNavIndex)
            {
                break;
            }
        }
        //最后一页
        let lastButton = getBtnDom(configData.pageNum, configData.pageNum);
        nav_Container.appendChild(lastButton);

    }else if(configData.pageNow >= configData.pageNum - centerNavIndex)
    {
        //第一个
        if(configData.singleNavNum < configData.pageNum){
            let startBtn = getBtnDom(1, 1);
            nav_Container.appendChild(startBtn);
        }
        //贴最右
        for(let i= 1; i <= configData.pageNum;i++){
            let newBtn = null;
            if(i <= configData.pageNum - configData.singleNavNum)
            {
                if(i == 1 && configData.singleNavNum < configData.pageNum){
                    newBtn = getBtnDom("…","…",false);
                    newBtn.className = "none";
                }else{
                    //跳过无用循环
                    i = configData.pageNum - configData.singleNavNum;
                    continue;
                }
            }else if(i == configData.pageNow){
                newBtn = getBtnDom(i,i,false);
                newBtn.className = "current";
            }else if(i < configData.pageNow || i <= configData.pageNow + centerNavIndex){
                newBtn = getBtnDom(i,i,true);
            }
            if(newBtn){
                nav_Container.appendChild(newBtn);
            }
        }
    }

    //"下一页"
    let nextBtn = getBtnDom("下一页", "下一页",true,nextPage);
    nav_Container.appendChild(nextBtn);
}



//生成按钮
function getBtnDom(index, content, regEvent = true, callback) {
    let nav_Btn = document.createElement("li");
    nav_Btn.innerText = content;
    if (regEvent) {
        nav_Btn.addEventListener("click", (ev) => {
            callback ? callback() : thePage(index);
        });
    }
    return nav_Btn;
}
//生成追番信息
function getItemDom(data){
    if(!data){
        return;
    }
    //console.log(data);
    let item = data;
    let ItemDom = document.createElement("div");
    ItemDom.className = "BangumiItem";
    
    let ItemUrl = document.createElement("a");
    ItemUrl.className = "BangumiUrl";
    ItemUrl.href = item.subject.url;
    ItemUrl.target = "_blank";

    let ItemImg = document.createElement("img");
    ItemImg.className = "BangumiImg";
    ItemImg.src = item.subject.images.common.replace("http:","");

    let ItemText = document.createElement("div");
    ItemText.className = "BangumiText";
    ItemText.innerHTML =  (item.subject.name_cn == "" ? item.name:item.subject.name_cn) + "<br>"
                          + item.name + "<br>"
                          + "首播日期:" + item.subject.air_date + "<br>";
    
    let ItemProgress = document.createElement("div");
    ItemProgress.className = "BangumiProgress";
    //计算观看进度
    let pAllNum = item.subject.eps;
    let pNowNum = item.ep_status;
    let pNumText = pNowNum  + "/" + pAllNum;
    let pFGWidth = 100;
    if(pAllNum != 0 && pAllNum !== "未知"){
        pFGWidth = pNowNum / pAllNum * 100;
    }
    
    let pText = document.createElement("div");
    pText.className = "ProgressText";
    pText.innerText = pNumText;

    let pProgress = document.createElement("div");
    pProgress.className = "ProgressFG";
    pProgress.style.width = pFGWidth + "%";

    ItemProgress.appendChild(pText);
    ItemProgress.appendChild(pProgress);

    ItemUrl.appendChild(ItemImg);
    ItemUrl.appendChild(ItemText);
    ItemUrl.appendChild(ItemProgress);

    ItemDom.appendChild(ItemUrl);

    return ItemDom;


}
function thePage(index) {
    if (thePage < 1 || thePage > configData.pageNum) {
        alert("参数非法");
        return;
    }
    configData.pageNow = index;
    JumpPage(index);
}

function nextPage() {
    if (configData.pageNow >= configData.pageNum) {
        //已经到后了。
        configData.pageNow = configData.pageNum;
        return;
    }
    configData.pageNow++;
    JumpPage(configData.pageNow);
}

function prevPage() {
    if (configData.pageNow <= 1) {
        //已经到最前了。
        configData.pageNow = 1;
        return;
    }
    configData.pageNow--;
    JumpPage(configData.pageNow);
}
