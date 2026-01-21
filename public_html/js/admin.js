function stop_waitMe(elm=null){
    if(elm==null) elm=$('body');
    else elm = $(elm);

    elm.waitMe('hide');
}

function run_waitMe(elm=null){

    if(elm==null) elm=$('body');
    else elm = $(elm);

    elm.waitMe({

        //none, rotateplane, stretch, orbit, roundBounce, win8,
        //win8_linear, ios, facebook, rotation, timer, pulse,
        //progressBar, bouncePulse or img
        effect: 'timer',

        //place text under the effect (string).
        text: 'Please wait...',

        //background for container (string).
        bg: 'rgba(255,255,255,0.7)',

        //color for background animation and text (string).
        color: '#000',

        //max size
        maxSize: '',

        //wait time im ms to close
        waitTime: -1,

        //url to image
        source: '',

        //or 'horizontal'
        textPos: 'vertical',

        //font size
        fontSize: '',

        // callback
        onClose: function() {}

    });
}

function formatMoney(money){
    let config = { style: 'currency', currency: 'VND', maximumFractionDigits: 9}
    return new Intl.NumberFormat('vi-VN', config).format(money);
}

function formatCurrency(value){
    var result=0;
    if (value && value.length >3){
        var numFormat = new Intl.NumberFormat("en-US");
        result= numFormat.format(value.replaceAll( ",","" ));
    }else{
        result= value;
    }
    return result;
}
function formatPrice(x) {
    return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ".");
}

function isEmailAddress(mail)
{
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
    {
        return (true)
    }
    //alert("You have entered an invalid email address!")
    return (false)
}

function subBarcode(number){
    try {
        return number.substr(-6);
    } catch (error) {
        return '';
    }
}