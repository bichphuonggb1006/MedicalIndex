   var meetingNumber = '85712752114';
   var apiKey = '3-Xr8kCNSZqgH2GG0G9cbg';
   var apiSecret = 'qCoH57o6qvPILg9aBURUwmy2rNQC6kfdzE87';
   var role = 0;
   var signature  = 'My1YcjhrQ05TWnFnSDJHRzBHOWNiZy45NzIyMDk4MDIxMC4xNjI5MDAyNjU5MDYxLjAuUXNacThrZE1TbXBEbEFVVDNlUHVkbkhqV1laMm1DSk50NCtzRGhsWkp0WT0';
   var username = "giang dep zai";
   var userEmail = "giangdoan164@gmail.com";
   var passWord = "2hsJwX";
    
window.addEventListener('DOMContentLoaded', function(event) {
  console.log('DOM fully loaded and parsed');
  
  var signature = ZoomMtg.generateSignature({
        meetingNumber: meetingNumber,
        apiKey: apiKey,
        apiSecret: apiSecret,
        role: role,
        success: function (res) {
		  console.log('generateSignature ket qua tra ve');
          console.log(res.result);
		  signature = res.result;
		  
     //     meetingConfig.signature = res.result;
        //  meetingConfig.apiKey = API_KEY;
        //  var joinUrl = "/meeting.html?" + testTool.serialize(meetingConfig);
        //  console.log(joinUrl);
       //   window.open(joinUrl, "_blank");
        },
     });
	   
  websdkready(signature);
});

function websdkready(signature) {

  var testTool = window.testTool;
  // get meeting args from url
  var tmpArgs = testTool.parseQuery();
 
 
  // a tool use debug mobile device
  //if (testTool.isMobileDevice()) {
   // vConsole = new VConsole();
  //}
  console.log('giang');
  console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

  // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
  // ZoomMtg.setZoomJSLib("https://source.zoom.us/1.9.7/lib", "/av"); // CDN version defaul
  console.log('----------');
  ZoomMtg.preLoadWasm();
  ZoomMtg.prepareJssdk();
  
  console.log('ahihi');
  function beginJoin(signature) {
    ZoomMtg.init({
      leaveUrl: "https://cuoi.tuoitre.vn/tin-tuc/mcdonalds-bi-kien-vi-quang-cao-vao-mua-chay-2021080996594158.html",
	  // debug: true,
	 
	  //tat in invite 
	  disableInvite: true,
	  //tat chat
	  // isSupportChat: false,
	  // tat chia se man hinh
	  screenShare : false,
	     //tat bat hien congig mic : nhung van co am thanh
	  // disableJoinAudio : false,
	  	  //config chon video : bat buoc true neu ko thi ko co video hien ???
	    // isSupportAV: true	 , //optional,
	  //MAN HINH TRUNG GIAN CHON MIC , AUDIO
	   disablePreview: true, // default false
      success: function () {
       // console.log(meetingConfig);
        console.log("signature yeah", signature);
        ZoomMtg.i18n.load("vi-VN");
        ZoomMtg.i18n.reload("vi-VN");
        ZoomMtg.join({
          meetingNumber: meetingNumber,
          userName: username,
          signature: 'My1YcjhrQ05TWnFnSDJHRzBHOWNiZy44NTcxMjc1MjExNC4xNjI5ODgzNTIyMjYxLjAuYXJ5UVpmR3BnRi9FWG1WQ2F5UlM3cm96OFhOaGw1ZURzYmM0RTFqSlZJMD0',
          apiKey: apiKey,
          userEmail: userEmail,
          passWord: passWord,
          success: function (res) {
            console.log("join meeting success");
            console.log("get attendeelist");
            ZoomMtg.getAttendeeslist({});
            ZoomMtg.getCurrentUser({
              success: function (res) {
                console.log("success getCurrentUser", res.result.currentUser);
              },
            });
          },
          error: function (res) {
            console.log(res);
          },
        });
      },
      error: function (res) {
        console.log(res);
      },
    });

    ZoomMtg.inMeetingServiceListener('onUserJoin', function (data) {
      console.log('inMeetingServiceListener onUserJoin', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onUserLeave', function (data) {
      console.log('inMeetingServiceListener onUserLeave', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onUserIsInWaitingRoom', function (data) {
      console.log('inMeetingServiceListener onUserIsInWaitingRoom', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
      console.log('inMeetingServiceListener onMeetingStatus', data);
    });
  }

  beginJoin();
};
