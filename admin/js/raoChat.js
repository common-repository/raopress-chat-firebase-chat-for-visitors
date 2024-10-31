var currentUserKey = jQuery('#current_role_id').val();;
var currentUserId = jQuery('#current_id').val();
var chatKey = '';
var friend_id = admin_user = '';
var hostnm = siteConfig.plugin_url;
var timestamp_data = [];
var user_html_data = [];
var admin_send = admin_clicked = false;

jQuery(document).ready(function ($) {
    var admin_acronym = jQuery('#admin_acronym').val();

    $('.wp_chatee_rao_dropdown_menu_options').click(function(){
        $(".wp_chatee_rao_dropdown_menu_toggle").slideToggle();
    });

    $('.wp_chatee_rao_attach_files').click(function(){
        $(".wp_chatee_rao_dropdown_attach_toggle").slideToggle();
    });

    $('.wp_chatee_rao_search_icon').click(function(){
        $(".wp_chatee_rao_search_toggle").slideToggle();
    });

    $("#wp_chatee_rao_search").on("keyup", function(){
        var value = $(this).val().toLowerCase();
        $('#wp_chatee_rao_chat_list li').filter( function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    // 	firebase.database().ref().update(updates);


    $(document).on('click','#wp_chatee_rao_message_list', function(e) {
        $('.wp_chatee_rao_dropdown_attach_toggle').fadeOut();
    });
    
    const chat_data_ref = firebase.database().ref("chat_data");
    chat_data_ref.on("child_changed", (snapshot, prevChildKey) => {
        var open_friend_id = $('#chatPanel').attr("data-chat-id");
        if(open_friend_id === snapshot.key)
        return;

     

        var updated_firebase_friend = snapshot.key;
        var badge_class = "badge_"+updated_firebase_friend;
        var badge_count = $('.'+badge_class).text();
        badge_count = parseInt(badge_count);
        badge_count = (badge_count + 1);
        
        $('#'+updated_firebase_friend).prependTo("#wp_chatee_rao_chat_list");
        $("."+badge_class).text(badge_count).fadeIn();
       
    });
    const friendlist_ref = firebase.database().ref("friend_list");
    friendlist_ref.on("child_added", (snapshot, prevChildKey) => {
        
    });
    firebase.database().ref('chat_data').on('child_added', (snap) => {
    
    firebase.database().ref('chat_data')
            .child(snap.key).child('updatedAt').on('value', (updateSnap) => {
            })
    });
    firebase.database().ref('friend_list').on('child_added', (snap) => {
        let snapshot = firebase.database().ref('users/' + snap.val().userId).child('email').get();
    });


jQuery('#txtMessage').keyup(function (key) {
    if (key.keyCode == 13) {
        SendMessage();
        jQuery(this).val('');
    }
});
jQuery("#wp_chatee_rao_send").on('click', function(e){
    e.preventDefault();
    SendMessage();
    jQuery("#txtMessage").val("");
});
// $("#txtMessage").keyup(function() {

//     if (key.which === 13) {
//         alert('The box is empty');
//       
//         $(this).val('');
//     }

// });

////////////////////////////////////////
function ChangeSendIcon(control) {
    if (control.value !== '') {
        document.getElementById('wp_chatee_rao_send').removeAttribute('style');
        document.getElementById('wp_chatee_rao_audio').setAttribute('style', 'display:none');
    }
    else {
        document.getElementById('wp_chatee_rao_audio').removeAttribute('style');
        document.getElementById('wp_chatee_rao_send').setAttribute('style', 'display:none');
    }
}

/////////////////////////////////////////////
// Audio record

let chunks = [];
let recorder;
var timeout;

/**
 * audio recording function
 * @param {*} control 
 */
function record(control) {
    let device = navigator.mediaDevices.getUserMedia({ audio: true });
    device.then(stream => {
        if (recorder === undefined) {
            recorder = new MediaRecorder(stream);
            recorder.ondataavailable = e => {
                chunks.push(e.data);

                if (recorder.state === 'inactive') {
                    let myblob = new Blob(chunks, { type: 'audio/mp3' });
                    var reader = new FileReader(myblob);
                    let binary = convertURIToBinary(reader.result);
                    let blob = new Blob([binary], {
                        type: 'audio/mp3'
                    });
                    const file = new File([blob], 'audio.mp3', { 'type': blob.type });
                    getStorageUrl(file);
                    reader.addEventListener("load", function (event) {
                    }, false);

                    reader.readAsDataURL(blob);
                }
            }
            recorder.start();
            control.innerHTML = `<i class="fas fa-stop"></i>`
        }
    });
    if (recorder !== undefined) {
        if (control.innerHTML == `<i class="fas fa-stop"></i>`) {
            recorder.stop();
            control.innerHTML = `<i class="fas fa-microphone"></i>`
        }
        else {
            chunks = [];
            recorder.start();
            control.innerHTML = `<i class="fas fa-stop"></i>`
        }
    }
}

/**
 * convert base64 to blob
 * @param {base64String} dataURI 
 * @returns binary array
 */
function convertURIToBinary(dataURI) {
    let BASE64_MARKER = ';base64,';
    let base64Index = dataURI.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
    let base64 = dataURI.substring(base64Index);
    let raw = window.atob(base64);
    let rawLength = raw.length;
    let arr = new Uint8Array(new ArrayBuffer(rawLength));

    for (let i = 0; i < rawLength; i++) {
        arr[i] = raw.charCodeAt(i);
    }
    return arr;
}

/**
 * Upload file to firebase storage
 * @param {fileToUpload} file 
 */
function getStorageUrl(file) {
    const ref = firebase.storage().ref();
    const name = (+new Date()) + '-' + file.name;
    const lastDot = file.name.lastIndexOf('.');
    const ext = file.name.substring(lastDot + 1);
    const metadata = {
        contentType: file.type
    };
    const task = ref.child(name).put(file, metadata);
    var currentUserId = jQuery('#current_id').val();
    task
        .then(snapshot => snapshot.ref.getDownloadURL())
        .then((url) => {
            var msgData = {
                date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
                is_read: 0,
                message_type: 'audio',
                msg: {
                    ext: ext,
                    mimeType: file.type,
                    name: file.name,
                    url: url
                },
                user_id: currentUserId,
                sender_id: currentUserId,
            }
            firebase.database().ref('chat_data/' + friend_id + '/message').push().set(msgData, function (error) {
                if (error) alert(error);
                else {
                    document.getElementById('txtMessage').text = '';
                    document.getElementById('txtMessage').focus();
                }
            });
        })
        .catch(console.error);
}

// Load All Emoji

function signIn() {
    var provider = new firebase.auth.GoogleAuthProvider();
    firebase.auth().signInWithPopup(provider);
}

function login() {

    var userEmail = $("#email_field").val();
    var userPass = $("#password_field").val();

    if (userEmail != "" && userPass != "" && userEmail !== undefined && userPass !== "undefined") {

        firebase.auth().signInWithEmailAndPassword(userEmail, userPass)
            .then((userCredential) => {
                // Signed in
                admin_user = userCredential.user;

                $.ajax({
                    url: siteConfig.ajaxurl,
                    type: "POST",
                    data: {
                        action: "add_chat_admin_name",
                        "display_name":  firebase.auth().currentUser.displayName
                    },
                    success: function(data) {
                        
                    } 
                });
            }).catch((error) => {
                $.ajax({
                    url: siteConfig.ajaxurl,
                    type: "POST",
                    data: {
                        action: 'remove_firebase_login',
                        "error": error.message,
                        },
                    success: function(data) {
                        location.reload();
                        }
                    
                })
            });

    } else {
        alert("Field is blank");
    }
}



function LoadChatList() {
    let init = true;

    var db = firebase.database().ref('friend_list').limitToLast(550);
   
    db.on('value', function (lists) {
        document.getElementById('wp_chatee_rao_chat_list').innerHTML = `<!--<li class="wp_chatee_rao_chat_item" style="background-color:#f8f8f8;">
                            <input type="text" placeholder="Search or new chat" class="form-control form-rounded" />
                        </li>-->`;

        var flist = []
        lists.forEach(function (data) {
            flist.push(data.val())
        });

       
        flist.reverse();
        user_html_data = [];
        //         lists.forEach(function (data) {
            var auto_selected = false;
            var first_key = "";
        flist.forEach(function (data, friendListIndex) {
            // 			[...lists].reverse().forEach(function (data) {
            var lst = data;
            
            var friendKey = '';
            if (lst.friendId === currentUserKey) {
                friendKey = lst.userId;
            }
            else if (lst.userId === currentUserKey) {
                friendKey = lst.friendId;
            }
            
//             alert(friendKey);
            if (friendKey !== "") {
               
                firebase.database().ref('users').child(friendKey).on('value', function (data) {
                    
                    var user = data.val();
                    auto_selected = true;
                    if(user == null)
                    return false;
                    if(user !== null)
                    var testvar = user.name;
                    else
                    var testvar = "";
                    
                    var chat_key = data.key;
                    // alert(typeof testvar);
                    
                    var db1 = firebase.database().ref('chat_data/' + friendKey + '/message');                    
                    var currentUserId = jQuery('#current_id').val();                   
                    var badge = '';
                    var last = {};
                    // alert(chatKey);
                    db1.on('value', function (chats) {
                       // let unreadMsg = []
                        let  unread_count = 0;
                        last = {};
                        chats.forEach(function (element, i) {
                            last = {};
                            var chat = element.val();
                            
                            if ( !chat.is_read ) {
                                
                                unread_count++;	
                         
                            }

                           
                            //timestamp_data[chat_key] = chat.date;
                           
                            if(chat.date)
                            last = { chatkey: chat_key, chat_date: chat.date};

                            
                        })
                        var badge_class = "badge_"+data.key;
                        if (unread_count > 0){
                            
                            badge = '<span class="badge '+badge_class+'" style="float:right;">' + unread_count + '</span>';
                        } else {
    
                            badge = '<span class="badge '+badge_class+'" style="float:right;display:none;">' + unread_count + '</span>';
                        }
                        
                        if (init === false) {
                
                            let allUsersHTML = document.getElementById("wp_chatee_rao_chat_list").children;
                            let allUsers = [];
                            
                            for (const [key, value] of Object.entries(allUsersHTML)) {
                              allUsers.push(value);
                            }
            
                            let newMessageUserIndex = allUsers.findIndex(
                              (obj) => obj.id == friendKey
                            );
                            
                            if(newMessageUserIndex > 0){
                                let newMessageUser = document.getElementById(friendKey);
                               
                                document.getElementById(friendKey).remove();
                                document.getElementById('wp_chatee_rao_chat_list').prepend(newMessageUser)
                            }
                            
                            // alert("Make this at top")
                          }
                    })
                    
                    timestamp_data.push(last);

                    //data.key is the chat key between user and firebase admin
                    var html_data = "";
                    var user_email = "";

                    if(typeof(user.email) != "undefined" && user.email !== null) {
                    user_email = user.email;
                    }

                    var acronym = user_name = photo_url = "";

                    if(typeof(user.photoURL != "undefined") && user.photoURL !== "") {
                        photo_url = user.photoURL;
                    }

                    if(typeof(user.name) != "undefined" && user.name !== null) {
                        user_name = user.name;                        
                        
                    }
                    //user_photoURL = firebase.auth().currentUser.photoURL
                    if(photo_url == "" || photo_url == undefined){
                        acronym = '';
                        if(user_name !== "")
                        acronym = user_name.replace(/ *\([^)]*\) */g, "").split(/\s/).reduce((response,word)=> response+=word.slice(0,1),'');
                        else
                        acronym = user_email.split(/\s/).reduce((response,word)=> response+=word.slice(0,1),'');
                        acronym = `<div class="wp_chatee_rao_profile_image"><div class="wp_chatee_rao_user_avatar_default">`+acronym+`</div></div>`;
                        }
                        else
                        acronym = `<div class="wp_chatee_rao_profile_image">`+photo_url+`</div>`;
                    if (user_email !== "") {
                        
                        
                        html_data = `<li id="${data.key}" class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data-user_email="${user_email}" data-user_name = "${user_name}" >
                            <!--<div class="row">
                                <div class="col-md-2"><img src="`+ hostnm + `/images/pp.png" class="friend-pic rounded-circle user" />                                
                                </div>-->
                                <div>
                                    `+acronym+`
                                    <div class="wp_chatee_rao_name" style="display:inline-block;">${user.name}</br><span>${user.email}</span></div>` + badge + `
                                    <!--<div class="wp_chatee_rao_under_name"></div>-->
                                </div>
                            <!--</div>-->
                        </li>`;
                    } else {
                        html_data =  `<li id="${data.key} class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data-user_email="${user_email}" data-user_name = "${user_name}">
                        <!--<div class="row">
                                <div class="col-md-2"><img src="${user.photoURL}" class="friend-pic rounded-circle " />
                                </div>-->
                                <div>
                                    `+acronym+`
                                    <div class="wp_chatee_rao_name">${user.name}</div>`+ badge + `
                                    <!--<div class="wp_chatee_rao_under_name"></div>-->
                                </div>
                            <!--</div>-->
                        </li>`;
                    }

                    var new_last = { chatkey: chat_key, "html_data": html_data};
                    user_html_data.push(new_last);

                });
                if (friendListIndex === flist.length - 1) {
                   
                    init = false;
                  }
                
            }
        });

        //alert("stat");
                $.ajax({
                    url: siteConfig.ajaxurl,
                    async:false,
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                    dataType: "JSON",
                    data: {
                        action: "sort_chat_keys",
                        type: "GET",
                        "timestamp_data": timestamp_data
                    },
                    success: function(response) {
                        var first_key = false;
                        var first_key_tigger = "";
                        response_sort = response.data;
                      
                        $.each(response.data,function(key,value) {
                            var current_key = key;
                           
                            user_html_data.forEach(function(chat_html_data){
                                
                                if(chat_html_data.chatkey === current_key) {
                               
                                if(!first_key)
                                {
                                    first_key_tigger = current_key;
                                }   
                                first_key = true;
                                    document.getElementById('wp_chatee_rao_chat_list').innerHTML += chat_html_data.html_data;
                                    //return false;
                                } 
                            });
                        });
                        //temporary commenting
                        //$(document).find("#"+first_key_tigger).trigger("click");
                    }
                });
    });
    
                
}

function LoadChatMessages(chatKey, friendKey,admin_clicked = false) {
    // var db = firebase.database().ref('chatMessages').child(chatKey);
    var user_acronym = $('#'+friendKey).find('.wp_chatee_rao_profile_image').html();
    let sameChat = false;
    
    var db = firebase.database().ref('chat_data/' + friendKey + '/message');
    
    var currentUserId = jQuery('#current_id').val();
    
    db.on('value', function (chats) {
       
        var messageDisplay = '';
        var today_date = moment().format("YYYY-MM-DD");
		var yesterday_date = moment().subtract(1,'days').format("YYYY-MM-DD");
		var date_loop_array = [];
        chats.forEach(function (data) {
            
            var chat = data.val();
            var date_format = moment(chat.date).format("YYYY-MM-DD");
			var time_format = moment(chat.date).format('HH:mm');

            var loop_date = "";
				if(date_loop_array.length === 0) {
					//this is the first date
					
					date_loop_array.push(date_format);
					loop_date = date_format;
				} else {
					if($.inArray(date_format,date_loop_array) !== -1) {
						
						loop_date = "";
					} else {
						
						loop_date = date_format;
						date_loop_array.push(loop_date);
					}
				}

			var friendUserEmail = jQuery('#wp_chatee_rao_chat_with_user').val();
			var currentFriendKey = localStorage.getItem('currentFriendKey')

			if (currentFriendKey === friendKey ) {
				sameChat = true;
				//if (chat.sender_id != currentUserId) {
                if (admin_clicked) {
                data.ref.update({
                    is_read: 1
                })
                }
           
            var dateTime = moment(chat.date).format('YYYY-MM-DD HH:mm:ss');
            var msg = '';
            if (chat.message_type === 'image') {
                msg = `<img src='${chat.msg.url}' class="img-fluid" />`;
            }
            else if (chat.message_type === 'audio') {
                msg = `<audio controls>
                        <source src="${chat.msg.url}" type="audio/webm" />
                    </audio>`;
            } else if (chat.message_type === 'latlon') {
                msg = `<iframe src="https://maps.google.com/maps?q=${chat.msg.lat}, ${chat.msg.lon}&z=15&output=embed" width="360" height="270" frameborder="0" style="border:0"></iframe>`;
            } else if (chat.message_type === 'file') {
                msg = `<a class="attach" href="${chat.msg.url}" target="_blank">${chat.msg.name}</a>`;
            } else if (chat.message_type === 'video') {
                msg = `<video controls width="360" height="270">
                           <source src="${chat.msg.url}" type="video/mp4" />
                       </video>`;
            }
            else {
                msg = chat.msg;
            }

            if(loop_date !== "") {
                if(loop_date === today_date)
                loop_date = "Today";
                if(loop_date === yesterday_date)
                loop_date = "Yesterday";
                
                var date_html = '<span class="chat_group_date chat_msg_item">'+loop_date+'</span>';
                
                messageDisplay += date_html;
            }

            if (chat.user_id != currentUserId) {
               
                    messageDisplay += `<div class="wp_chatee_rao_message_single wp_chatee_rao_receive">
                                    `+user_acronym+`
                                    <div class="wp_chatee_rao_message_inner">`+
                                        //<button data-id="` + data.key + `" onclick='deleteMessagetest(this);' class="fas fa-trash icon" id="buttondelete" style="background: transparent;border: none;color: #f44336; font-size: 12px;"></button>
                                        `<p>
                                            ${msg}
                                            <span class="wp_chatee_rao_message_time" title="${time_format}">${time_format}</span>
                                        </p>
                                    </div>
                                </div>`;
               
            } else {
                messageDisplay += `<div class="wp_chatee_rao_message_single wp_chatee_rao_sent">
                            `+admin_acronym+`
                            <div class="wp_chatee_rao_message_inner">`+
                                //<button data-id="` + data.key + `" onclick='deleteMessagetest(this);' class="fas fa-trash icon" id="buttondelete" style="background: transparent;border: none;color: #f44336; font-size: 12px;"></button>
                                `<p>
                                    ${msg}
                                    <span class="wp_chatee_rao_message_time" title="${time_format}">${time_format}</span>
                                </p>
                            </div>
                        </div>`;
            }
			} else {
				sameChat = false;
			}
            
        });
		if(sameChat) {
		document.getElementById('wp_chatee_rao_message_list').innerHTML = messageDisplay;
        document.getElementById('wp_chatee_rao_message_list').scrollTo(0, document.getElementById('wp_chatee_rao_message_list').scrollHeight);	
		}
        
    });

    var badge_class = "badge_"+friendKey;
    
    $('.'+badge_class).text(0).fadeOut();
    //$('#'+friendKey).prependTo("#wp_chatee_rao_chat_list");
}

function onFirebaseStateChanged() {
    firebase.auth().onAuthStateChanged(onStateChanged);
}

function onStateChanged(admin_user) {
    
    var email = jQuery("#email_field").val();
    var name = jQuery("#name_field").val();

    if (admin_user) {
        // alert(firebase.auth().currentUser.email + '\n' + firebase.auth().currentUser.displayName);
        var user = email;
        var userProfile = { email: '', name: '', photoURL: '' };
        userProfile.email = email;
        userProfile.name = name;
        userProfile.photoURL = firebase.auth().currentUser.photoURL;
        
        var db = firebase.database().ref('users');
        
        var flag = false;
        db.on('value', function (users) {
            users.forEach(function (data) {

                var user = data.val();
                
                if (user.email === userProfile.email) {
                    currentUserKey = data.key;
                    flag = true;
                    jQuery.ajax({
                        url: siteConfig.ajaxurl,
                        type: "POST",
                        data: {
                            action: "add_chat_admin_id",
                            "chat_id": currentUserKey
                        },
                        success: function(data) {
                          
                        } 
                    });
                }
            });
            // 			alert(currentUserKey);
            // 			document.getElementById("current_role_id").value = currentUserKey;

            if (flag === false) {
                if(userProfile.email !== undefined)
                firebase.database().ref('users').push(userProfile, callback);
            }
            else {
               
            }
           
            LoadChatList();
            //s  NotificationCount();
        });
    }
    else {
        // document.getElementById('imgProfile').src = hostnm + '/images/pp.png';
        // document.getElementById('imgProfile').title = '';

        // document.getElementById('lnkSignIn').style = '';

        // document.getElementById('lnkNewChat').classList.add('disabled');
/*
       $('#login_div').fadeOut();
        $('#user_div').fadeOut();
        $('#side-1').remove();
        error = "";
        $.ajax({
            url: siteConfig.ajaxurl,
            type: "POST",
            data: {
                action: 'remove_firebase_login',
                "error": error
                },
            success: function(data) {
                //location.reload();
                }
            
        });*/
    }
}

function callback(error) {
    if (error) {
        alert(error);
    }
    else {
        document.getElementById('imgProfile').src = firebase.auth().currentUser.photoURL;
        // document.getElementById('imgProfile').src = hostnm + '/images/pp.png';
        // document.getElementById('imgProfile').title = firebase.auth().currentUser.displayName;
        // document.getElementById('lnkSignIn').style = 'display:none';
    }
}

$(document).on('click','.wp_chatee_rao_chat_item', function(e){
    e.preventDefault();
    var friendKey = $(this).attr("id");
    var friendEmail = $(this).data("user_email");
    var friendName = $(this).data("user_name");
    $(".wp_chatee_rao_chat_item").removeClass("active");
    $("#"+friendKey).addClass("active"); 
    $('.wp_chatee_rao_dropdown_attach_toggle').fadeOut();
    
    friend_id = friendKey;
    
	localStorage.setItem('currentFriendKey', friendKey)
    
    var friendList = { friendId: friendKey, userId: currentUserKey };
    
    var db = firebase.database().ref('friend_list');
    var flag = false;
    db.once('value', function (friends) {
        friends.forEach(function (data) {
            var user = data.val();
    
            if ((user.friendId === friendList.friendId && user.userId === friendList.userId) || ((user.friendId === friendList.userId && user.userId === friendList.friendId))) {
                flag = true;
                chatKey = data.key;
            }
        });
        

        if (flag === false) {
        

            chatKey = firebase.database().ref('friend_list').push(friendList, function (error) {
                if (error) alert(error);
                else {
                    document.getElementById('wp_chatee_rao_chat_panel').removeAttribute('style');
                    document.getElementById('wp_chatee_rao_start_chat_screen').setAttribute('style', 'display:none');
                    $('#side-1').addClass('wp_chatee_rao_hide').removeClass('wp_chatee_rao_show');
                    $('#side-2').addClass('wp_chatee_rao_show').removeClass('wp_chatee_rao_hide');
                }
            }).getKey();
            
        }
        else {
            document.getElementById('wp_chatee_rao_chat_panel').removeAttribute('style');
            document.getElementById('wp_chatee_rao_start_chat_screen').setAttribute('style', 'display:none');
            $('#side-1').addClass('wp_chatee_rao_hide').removeClass('wp_chatee_rao_show');
            $('#side-2').addClass('wp_chatee_rao_show').removeClass('wp_chatee_rao_hide');
        }
        //////////////////////////////////////
        //display friend name and photo
        //alert(friendName);
        if (friendEmail == 'undefined') {
            document.getElementById('wp_chatee_rao_chat_with_user').innerHTML = 'User';
            // document.getElementById('imgChat').src = hostnm + '/images/pp.png';
        } else {
            document.getElementById('wp_chatee_rao_chat_with_user').innerHTML = friendName +'<span>'+ friendEmail +'</span>';
            // document.getElementById('imgChat').src = hostnm + '/images/pp.png';
        }
        // document.getElementById('wp_chatee_rao_chat_last_seen').innerHTML = "";
        // document.getElementById('deletbtn').setAttribute("data-id", chatKey);
        //alert(chatKey);

        document.getElementById('wp_chatee_rao_message_list').innerHTML = '';
        document.getElementById('txtMessage').text = '';
        document.getElementById('txtMessage').focus();
        ////////////////////////////
        // Display The chat messages
        
        admin_clicked = true;


        $('#chatPanel').attr("data-chat-id",friendKey);
        
        LoadChatMessages(chatKey, friendKey,admin_clicked);
        /*if(user_acronym !== "") {
            document.getElementById('wp_chatee_rao_chat_with_user_avatar').innerHTML = user_acronym;

        }*/
       
        document.getElementById('wp_chatee_rao_chat_with_user_avatar').innerHTML = $('#'+friendKey).find('.wp_chatee_rao_profile_image').html();
    
    });
});
if(jQuery("#current_role").val() == "notaccess"){
    signOut();
  } else{
    login();
  } 
loadAllEmoji();
// Call auth State changed
onFirebaseStateChanged();

 
});

function loadAllEmoji() {
    var emoji = '';
    for (var i = 128512; i <= 128566; i++) {
        emoji += `<a href="#" style="font-size: 22px;" onclick="getEmoji('&#${i}')">&#${i};</a>`;
    }	
	jQuery('#wp_chatee_rao_smiley').append(emoji);
    //jQuery('#wp_chatee_rao_smiley').html = emoji;
}
function showEmojiPanel() {
    jQuery("#wp_chatee_rao_send_emoji_panel").slideToggle();
}

function hideEmojiPanel() {
    document.getElementById('wp_chatee_rao_send_emoji_panel').setAttribute('style', 'display:none;');
}

function getEmoji(control) {
	//console.log(control);
   // jQuery('#txtMessage').append(control.innerHTML);
	 jQuery("#txtMessage").val(function() {
        return this.value + control;
    });
}

function StartChat(friendKey, friendEmail, friendName) {
    $(".wp_chatee_rao_chat_item").removeClass("active");
    $("#"+friendKey).addClass("active"); 
    $('.wp_chatee_rao_dropdown_attach_toggle').fadeOut();
    //     alert(friendKey)
    // if (jQuery('#current_role_ids').val() != "") {
    //     var currentUserKey = jQuery('#current_role_id').val();
    // } else {
    //     var currentUserKey = friendKey;
    // }
    //
    friend_id = friendKey;
	localStorage.setItem('currentFriendKey', friendKey)
    
    var friendList = { friendId: friendKey, userId: currentUserKey };
    
    var db = firebase.database().ref('friend_list');
    var flag = false;
    db.once('value', function (friends) {
        friends.forEach(function (data) {
            var user = data.val();
            if ((user.friendId === friendList.friendId && user.userId === friendList.userId) || ((user.friendId === friendList.userId && user.userId === friendList.friendId))) {
                flag = true;
                chatKey = data.key;
            }
        });
       

        if (flag === false) {
            chatKey = firebase.database().ref('friend_list').push(friendList, function (error) {
                if (error) alert(error);
                else {
                    document.getElementById('wp_chatee_rao_chat_panel').removeAttribute('style');
                    document.getElementById('wp_chatee_rao_start_chat_screen').setAttribute('style', 'display:none');
                    $('#side-1').addClass('wp_chatee_rao_hide').removeClass('wp_chatee_rao_show');
                    $('#side-2').addClass('wp_chatee_rao_show').removeClass('wp_chatee_rao_hide');
                }
            }).getKey();
            
        }
        else {
            document.getElementById('wp_chatee_rao_chat_panel').removeAttribute('style');
            document.getElementById('wp_chatee_rao_start_chat_screen').setAttribute('style', 'display:none');
            $('#side-1').addClass('wp_chatee_rao_hide').removeClass('wp_chatee_rao_show');
            $('#side-2').addClass('wp_chatee_rao_show').removeClass('wp_chatee_rao_hide');
        }
        //////////////////////////////////////
        //display friend name and photo
        //alert(friendName);
        if (friendEmail == 'undefined') {
            document.getElementById('wp_chatee_rao_chat_with_user').innerHTML = 'User';
            // document.getElementById('imgChat').src = hostnm + '/images/pp.png';
        } else {
            document.getElementById('wp_chatee_rao_chat_with_user').innerHTML = friendName +'<span>'+ friendEmail +'</span>';
            // document.getElementById('imgChat').src = hostnm + '/images/pp.png';
        }
        // document.getElementById('wp_chatee_rao_chat_last_seen').innerHTML = "";
        // document.getElementById('deletbtn').setAttribute("data-id", chatKey);
        //alert(chatKey);

        document.getElementById('wp_chatee_rao_message_list').innerHTML = '';
        document.getElementById('txtMessage').text = '';
        document.getElementById('txtMessage').focus();
        
        admin_clicked = true;


        $('#chatPanel').attr("data-chat-id",friendKey);
        
        LoadChatMessages(chatKey, friendKey,admin_clicked);
        /*if(user_acronym !== "") {
            document.getElementById('wp_chatee_rao_chat_with_user_avatar').innerHTML = user_acronym;

        }*/
       
        document.getElementById('wp_chatee_rao_chat_with_user_avatar').innerHTML = $('#'+friendKey).find('.wp_chatee_rao_profile_image').html();
    });
}





function hideShowChatList() {
    $('#side-1').toggleClass('wp_chatee_rao_show wp_chatee_rao_hide');
    $('#side-2').toggleClass('wp_chatee_rao_show wp_chatee_rao_hide');
}

function SendMessage() {
    jQuery('.wp_chatee_rao_dropdown_attach_toggle').fadeOut();
    var current_friend = jQuery('#chatPanel').attr('data-chat-id');
    admin_send = true;
    var message = jQuery('#txtMessage').val();
    //alert(message);
    if(message == "")
    return;
    var currentUserId = jQuery('#current_id').val();
    var msgData = {
        date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
        is_read: 0,
        message_type: 'text',
        msg: message,
        user_id: currentUserId,
        sender_id: currentUserId,
    }

    var message = firebase.database().ref('chat_data/' + friend_id + '/message').push()
    message.set(msgData);
    
    document.getElementById('txtMessage').val = '';
    document.getElementById('txtMessage').focus();
    jQuery(document).find("#"+friend_id).trigger("click");
    jQuery('#'+current_friend).prependTo("#wp_chatee_rao_chat_list");
    //         }
    //     });
}

//Send image
function ChooseImage() {
    document.getElementById('imageFile').click();
}

function SendImage(event) {
    const ref = firebase.storage().ref();
    var file = event.files[0];
    const lastDot = file.name.lastIndexOf('.');
    const ext = file.name.substring(lastDot + 1);
    var currentUserId = jQuery('#current_id').val();
    const metadata = {
        contentType: file.type
    };
    const name = (+new Date()) + '-' + file.name;
    const task = ref.child(name).put(file, metadata);
    if (!file.type.match("image.*")) {
        alert("Please select image only.");
    }
    else {
       
        task
            .then(snapshot => snapshot.ref.getDownloadURL())
            .then((url) => {
                var msgData = {
                    date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
                    is_read: 0,
                    message_type: 'image',
                    msg: {
                        ext: ext,
                        mimeType: file.type,
                        name: file.name,
                        url: url
                    },
                    user_id: currentUserId,
                    sender_id: currentUserId,
                };
                firebase.database().ref('chat_data/' + friend_id + '/message').push().set(msgData, function (error) {
                    
                    if (error) alert(error);
                    else {
                        document.getElementById('txtMessage').val = '';
                        document.getElementById('txtMessage').focus();
                    }
                });
            })
            .catch(console.error);
    }
}

function SendLink(map1_lat, map1_long) {
    //var map1_lat = getElementById("textlat").value;
    // var map1_long = getElementById("textlang").value;
    var currentUserId = jQuery('#current_id').val();
    var test_map = '<a href="http://maps.google.com/?q=' + map1_lat + ',' + map1_long + '" target="_blank">http://maps.google.com/?q=' + map1_lat + ',' + map1_long + '</a>';
    //     alert(test_map);

    //test_map
    var chatMessage = {
        userId: currentUserKey,
        msg: test_map,
        msgType: 'normal',
        dateTime: moment(new Date()).format('YYYY-MM-DD HH:mm:ss')
    };
    var msgData = {
        date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
        is_read: 0,
        message_type: 'latlon',
        msg: {
            lat: map1_lat,
            lon: map1_long,
        },
        user_id: currentUserId,
        sender_id: currentUserId,
    }

    firebase.database().ref('chat_data/' + friend_id + '/message').push().set(msgData, function (error) {
        if (error) alert(error);
        else {
            document.getElementById('txtMessage').val = '';
            document.getElementById('txtMessage').focus();
        }
    });
    
}

//Send file
function Choosefile() {
    document.getElementById('allFile').click();
}

function SendFile(event) {
    var file = event.files[0];

    //if (!file.type.match("image.*")) {
    // alert("Please select image only.");
    //}
    //else {
    var reader = new FileReader();

    reader.addEventListener("load", function () {
        var chatMessage = {
            userId: currentUserKey,
            msg: file.name,
            msgType: 'pdf',
            dateTime: moment(new Date()).format('YYYY-MM-DD HH:mm:ss')
        };
        //add new code
        var storageRef = firebase.storage().ref();
        //dynamically set reference to the file name
        var thisRef = storageRef.child(file.name);

        //put request upload file to firebase storage
        thisRef.put(file).then(function (snapshot) {
            alert("File Uploaded")
        });
        //stop new code

        firebase.database().ref('chatMessages').child(chatKey).push(chatMessage, function (error) {
            if (error) alert(error);
            else {
                document.getElementById('txtMessage').val = '';
                document.getElementById('txtMessage').focus();
            }
        });
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
    //}
}

function uploadFile() {

    const ref = firebase.storage().ref();
    const file = document.querySelector('#photo').files[0];
    const name = (+new Date()) + '-' + file.name;
    const lastDot = file.name.lastIndexOf('.');
    const ext = file.name.substring(lastDot + 1);
    const metadata = {
        contentType: file.type
    };
    const task = ref.child(name).put(file, metadata);
    var currentUserId = jQuery('#current_id').val();
    task
        .then(snapshot => snapshot.ref.getDownloadURL())
        .then((url) => {
            var msgData = {
                date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
                is_read: 0,
                message_type: 'file',
                msg: {
                    ext: ext,
                    mimeType: file.type,
                    name: file.name,
                    url: url
                },
                user_id: currentUserId,
                sender_id: currentUserId,
            }
            firebase.database().ref('chat_data/' + friend_id + '/message').push().set(msgData, function (error) {
                if (error) alert(error);
                else {
                    document.getElementById('txtMessage').val = '';
                    document.getElementById('txtMessage').focus();
                }
            });
        })
        .catch(console.error);
}



function PopulateUserList() {
    document.getElementById('lstUsers').innerHTML = `<div class="text-center">
                                                         <span class="spinner-border text-primary mt-5" style="width:7rem;height:7rem"></span>
                                                     </div>`;
    var db = firebase.database().ref('users');
    var dbNoti = firebase.database().ref('notifications');
    var lst = '';
    db.on('value', function (users) {
        if (users.hasChildren()) {
            lst = `<!--<li class="wp_chatee_rao_chat_item" style="background-color:#f8f8f8;">
                            <input type="text" placeholder="Search or new chat" class="form-control form-rounded" />
                        </li>-->`;
            document.getElementById('lstUsers').innerHTML = lst;
        }
        users.forEach(function (data) {
            var user = data.val();
            if (user.email !== firebase.auth().currentUser.email) {
                dbNoti.orderByChild('sendTo').equalTo(data.key).on('value', function (noti) {
                    if (noti.numChildren() > 0 && Object.values(noti.val())[0].sendFrom === currentUserKey) {

                        lst = `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="${user.photoURL}" class="rounded-circle friend-pic" />
                                </div>
                                <div class="col-md-10" style="cursor:pointer;">
                                    <div class="name">${user.name}
                                        <button class="btn btn-sm btn-defualt" style="float:right;"><i class="fas fa-user-plus"></i> Sent</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                        document.getElementById('lstUsers').innerHTML += lst;
                    }
                    else {
                        dbNoti.orderByChild('sendFrom').equalTo(data.key).on('value', function (noti) {
                            if (noti.numChildren() > 0 && Object.values(noti.val())[0].sendTo === currentUserKey) {
                                lst = `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="${user.photoURL}" class="rounded-circle friend-pic" />
                                </div>
                                <div class="col-md-10" style="cursor:pointer;">
                                    <div class="name">${user.name}
                                        <button class="btn btn-sm btn-defualt" style="float:right;"><i class="fas fa-user-plus"></i> Pending</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                                document.getElementById('lstUsers').innerHTML += lst;
                            }
                            else {
                                var urllistd_user = user.photoURL;
                                //alert(urllistd);
                                if (urllistd_user == undefined) {
                                    lst = `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data>
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="`+ hostnm + `/images/pp.png" class="rounded-circle friend-pic test3" />
                                </div>
                                <div class="col-md-10" style="cursor:pointer;">
                                    <div class="name">${user.email}
                                        <button onclick="SendRequest('${data.key}')" class="btn btn-sm btn-primary" style="float:right;"><i class="fas fa-user-plus"></i> Send Request</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;

                                    document.getElementById('lstUsers').innerHTML += lst;
                                } else {
                                    lst = `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data>
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="${user.photoURL}" class="rounded-circle friend-pic test3" />
                                </div>
                                <div class="col-md-10" style="cursor:pointer;">
                                    <div class="name">${user.name}
                                        <button onclick="SendRequest('${data.key}')" class="btn btn-sm btn-primary" style="float:right;"><i class="fas fa-user-plus"></i> Send Request</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;

                                    document.getElementById('lstUsers').innerHTML += lst;
                                }
                            }
                        });
                    }
                });
            }
        });
    });

}

function SendRequest(key) {
    let notification = {
        sendTo: key,
        sendFrom: currentUserKey,
        name: firebase.auth().currentUser.displayName,
        photo: firebase.auth().currentUser.photoURL,
        dateTime: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
        status: 'Pending'
    };

    firebase.database().ref('notifications').push(notification, function (error) {
        if (error) alert(error);
        else {
            // do something
            PopulateUserList();
        }
    });
}

function PopulateNotifications() {
    document.getElementById('lstNotification').innerHTML = `<div class="text-center">
                                                         <span class="spinner-border text-primary mt-5" style="width:7rem;height:7rem"></span>
                                                     </div>`;
    var db = firebase.database().ref('notifications');
    var lst = '';
    db.orderByChild('sendTo').equalTo(currentUserKey).on('value', function (notis) {
        if (notis.hasChildren()) {
            lst = `<!--<li class="wp_chatee_rao_chat_item" style="background-color:#f8f8f8;">
                            <input type="text" placeholder="Search or new chat" class="form-control form-rounded" />
                        </li>-->`;
        }
        notis.forEach(function (data) {
            var noti = data.val();
            if (noti.status === 'Pending') {
                lst += `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="${noti.photo}" class="rounded-circle friend-pic" />
                                </div>
                                <div class="col-md-10" style="cursor:pointer;">
                                    <div class="name">${noti.name}
                                        <button onclick="Reject('${data.key}')" class="btn btn-sm btn-danger" style="float:right;margin-left:1%;"><i class="fas fa-user-times"></i> Reject</button>
                                        <button onclick="Accept('${data.key}')" class="btn btn-sm btn-success" style="float:right;"><i class="fas fa-user-check"></i> Accept</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;
            }
        });
        document.getElementById('lstNotification').innerHTML = lst;
    });
}

function Reject(key) {
    let db = firebase.database().ref('notifications').child(key).once('value', function (noti) {
        let obj = noti.val();
        obj.status = 'Reject';
        firebase.database().ref('notifications').child(key).update(obj, function (error) {
            if (error) alert(error);
            else {
                // do something
                PopulateNotifications();
            }
        });
    });
}

function Accept(key) {
    let db = firebase.database().ref('notifications').child(key).once('value', function (noti) {
        var obj = noti.val();
        obj.status = 'Accept';
        firebase.database().ref('notifications').child(key).update(obj, function (error) {
            if (error) alert(error);
            else {
                // do something
                PopulateNotifications();
                var friendList = { friendId: obj.sendFrom, userId: obj.sendTo };
                firebase.database().ref('friend_list').push(friendList, function (error) {
                    if (error) alert(error);
                    else {
                        //do Something
                    }
                });
            }
        });
    });
}

function PopulateFriendList() {
    //alert('PopulateFriendList');
    //console.group(" PopulateFriendList ")

    // Loader Starts
    document.getElementById('lstFriend').innerHTML = `<div class="text-center">
                                                         <span class="spinner-border text-primary mt-5" style="width:7rem;height:7rem"></span>
                                                     </div>`;

    // Database Connection and get users
    var db = firebase.database().ref('users');
    var lst = '';

    db.on('value', function (users) {

        if (users.hasChildren()) {
            lst = `<!--<li class="wp_chatee_rao_chat_item" style="background-color:#f8f8f8;">
                            <input type="text" placeholder="Search or new chat" class="form-control form-rounded" />
                        </li>-->`;
        }

        users.forEach(function (data) {
            var user = data.val();
            // if (user.email !== firebase.auth().currentUser.email) {
            var userEmail = document.getElementById("email_field").value;

            if(user.email != userEmail )
            {
            var urllistd = user.photoURL;
            //alert(urllistd);
            if (urllistd == undefined) {
                //alert(yes);
                lst += `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data-user_email="${user_email}" data-user_name = "${user_name}">
                            <!--<div class="row">
                                <div class="col-md-2">
                                <img src="`+ hostnm + `/images/pp.png" class="rounded-circle friend-pic testtt" />
                                </div>-->
                                <div style="cursor:pointer;">
                                    <div class="name">${user.email}</div>
                                </div>
                            <!--</div>-->
                        </li>`;

            } else {
                lst += `<li class="wp_chatee_rao_chat_item wp_chatee_rao_chat_item_action" data-user_email="${user_email}" data-user_name = "${user_name}">
                            <!--<div class="row">
                                <div class="col-md-2">
                                <img src="${user.photoURL}" class="rounded-circle friend-pic" />
                                </div>-->
                                <div style="cursor:pointer;">
                                    <div class="name">${user.email}</div>
                                </div>
                            <!--</div>-->
                        </li>`;
            }
            // }
            }
        });
        document.getElementById('lstFriend').innerHTML = lst;
    });
    console.groupEnd()
}

function signOut(error = "") {
    firebase.auth().signOut();
    
    jQuery.ajax({
        url: siteConfig.ajaxurl,
        type: "POST",
        data: {
            action: 'remove_firebase_login',
            "error": error
            },
        success: function(data) {
            location.reload();
            }
        
    });
}