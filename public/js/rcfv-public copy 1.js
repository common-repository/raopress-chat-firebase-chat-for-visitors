var currentUserKey,friendkey;
var device_token = [];
firebase.initializeApp(firebaseConfig);
var current_url = jQuery(location).attr('href');
var session_html = '<p class="rcfv-referrer">This message was sent from url: '+current_url+'</p>';
var is_new_session = Cookies.get("rcfv_chat_session");
if(is_new_session === undefined) {
	is_new_session = true;
}
else{
	is_new_session = false;
}
jQuery(document).ready(function ($) {
var current_admin_id = $("#current_admin_id").val();

if(current_admin_id !== "" && current_admin_id !== undefined ) {
	//get admin token
	var users = firebase.database().ref("users");
	users.on('value', function (users) {
		users.forEach(function (data) {
		var user = data.val();
		
		if(data.key === current_admin_id) {
               if(user.isAdmin !== undefined && user.isAdmin) {
				
				if(user.tokens !== undefined) {
					device_token = user.tokens;
				
				}
			   }
		return false;
		}
		});
	});
	
	var admin_user = users.orderByChild("isAdmin").equalTo(true);
	
}

get_current_user_data();

function get_current_user_data() {
	$.ajax({
		url: siteConfig.ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: {
				action: "check_current_user",
				type: "GET",
			},
			success: function(response) {
				
				var response_data = JSON.parse(response.data);
				if( response_data.key !== "" ) {
					//user is logged in
					
					$("#current_role_id").val(response_data.key);
					$("#current_id").val(response_data.id);
					$('.chat_login').remove();
					$('.chat_converse').fadeIn();
					$('.chat_bottom').fadeIn();
					$('.chat_bottom').addClass("rfcv-actions");
					$('.fabs').fadeIn();
					currentUserKey = jQuery('#current_role_id').val();
					chat_id = chatKey = friendkey = currentUserKey;
					var chat_id = siteConfig.chat_id;
					var email = siteConfig.email;

					if( chat_id !== "" && email !== "" ) {
						//Load chat messages
						localStorage.setItem('currentFriendKey', friendkey)
						
						
						LoadChatMessages(chatKey,friendkey);
					}
					var chat_db = firebase.database().ref('chat_data/' + friendkey + '/message');
					chat_db.on('child_added', (snapshot, prevChild) => {
						load_new_child(snapshot);
					});

					chat_db.on('child_removed', (snapshot) => {
						remove_child(snapshot);
					});

				} else {
					$(".fabs").fadeIn();
					//user is not logged in
					$(".chat_login").fadeIn();
					$('.chat_converse').fadeOut();
					$('.chat_bottom').fadeOut();
					
				}
				
			}
	})
}





function load_new_child(snapshot) {
	chat_key_data = snapshot.key;
	chat_val_data = snapshot.val();
	var admin_user = 1;
	var msg = chat_val_data.msg;
	var msg_html = get_message_html(msg, chat_val_data.message_type);
	var time_format = moment(chat_val_data.date).format('HH:mm:ss');
	var latest_chat_message = "";
	
	if(admin_user !== parseInt(chat_val_data.user_id)) {
		//chat message from user
		latest_chat_message = `<span id="`+chat_key_data+`" class="chat_msg_item chat_msg_item_user"><span class="msg">${msg_html}</span><span class="time_status">${time_format}</span></span>`;
	} else {
		//chat messahe from admin
		latest_chat_message = `<span id="`+chat_key_data+`" class="chat_msg_item chat_msg_item_admin"><span class="msg">${msg_html}</span><span class="time_status">${time_format}</span></span>`;
	}
	$('.raocircle').remove();
	$('#chat_converse').append(latest_chat_message);
	document.getElementById('chat_converse').scrollTo(0, document.getElementById('chat_converse').scrollHeight);	
}


function remove_child(snapshot) {
	delete_key = snapshot.key;
	$('#'+delete_key).remove();
}

jQuery('#txtMessage').keyup(function (key) {
	if (key.keyCode == 13) {
		SendMessage();
		jQuery(this).val('');
	}
});

jQuery('#chatSend').keyup(function (key) {
	if (key.keyCode == 13) {
		SendMessage();
		jQuery(this).val('');
	}
});

jQuery('#fab_send').on('click', function(e){
	e.preventDefault();
	SendMessage();
	jQuery('#chatSend').val('');

});

jQuery('#fab_attachment').on('click',function(e){
	e.preventDefault();
	document.getElementById('imageFile').click();
});


$('#anonymus_submit').on('click', function(e){
	e.preventDefault();
	var name = $('#user_name').val() + " ( Guest User )";
	var email = $('#user_email').val();
	var status = true;
	$('#anonymus_submit').attr("disabled",true);
	$('#pageloader').css('display', 'block');
	var nonce = $('#_wpnonce').val();
	$('.chat_login').remove();
	
	if(undefined === firebase || undefined === firebase.database)
	{
		status = false;
		
	} 
	update_status(status);
	if(!status)
	return;
	if(name == "" || email == "")
	return;
	else {
		$.ajax({
			url: siteConfig.ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: {
				action: "create_anonymus_user",
				type: "POST",
				_wpnonce: nonce,
				_wp_http_referer: $('input[name=_wp_http_referer]').val(),
				name: name,
				email: email
			},
			success: function(response) {
				
				chat_id = response.data;
				friendkey = chat_id;
				localStorage.setItem('currentFriendKey', friendkey)
				Cookies.set('rao_anonymmus_friend_key',friendkey);
				
				var chatKey = chat_id;
				var friendName = email;
				$('#current_role_id').val(chatKey);
				
				sendMessageFromAdmin(chatKey,friendkey,true);
				$('#pageloader').css('display', 'none');  
				
				
				
				//$('#chat_head').html("Welcome, "+name);
				$('#chat_converse').fadeIn();
				$('.fab_field').css("display","flex");
				
				
				$('#anonymus_submit').attr("disabled",false);
			}
		}); 
	}
	//return false;
});

function LoadChatMessages(chatkey, friendKey, anonym) {
	var admin_user_id = 1;
	let sameChat = false;
	//$('#chat_converse').html("loading...");
	$('#chat_converse').append('<span class="chat_msg_item chat_msg_item_user raocircle"></span>');
	var chat_db = firebase.database().ref('chat_data/' + friendkey + '/message');
	
	if(anonym) {
		chat_db.on('child_added', (snapshot, prevChild) => {
			load_new_child(snapshot);
		});
		chat_db.on('child_removed', (snapshot) => {
			remove_child(snapshot);
		});
		chat_db.once('value', function (chats) {
			var chat_messages = "";
			var today_date = moment().format("YYYY-MM-DD");
			var yesterday_date = moment().subtract(1,'days').format("YYYY-MM-DD");
			var date_loop_array = [];
			
			chats.forEach(function (data) {
				
				var chat = data.val();
				var li_id = data.key;
				var date_format = moment(chat.date).format("YYYY-MM-DD");
				var time_format = moment(chat.date).format('HH:mm');
				var msg = '';
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
				
				
				var currentFriendKey = localStorage.getItem('currentFriendKey')
	
				if (currentFriendKey === friendKey ) {
					sameChat = true;
					
				
				if (chat.message_type === 'image') {
					msg = `<a href="${chat.msg.url}" target="_blank"><img src='${chat.msg.url}' class="img-fluid attach" /></a>`;
				}
				else if (chat.message_type === 'audio') {
					msg = `<audio controls>
							<source src="${chat.msg.url}" type="audio/webm" />
						</audio>`;
				} else if (chat.message_type === 'latlon') {
					msg = `<iframe src="https://maps.google.com/maps?q=${chat.msg.lat}, ${chat.msg.lon}&z=15&output=embed" width="360" height="270" frameborder="0" style="border:0"></iframe>`;
				} else if (chat.message_type === 'file') {
					//msg = `<iframe src="https://docs.google.com/viewerng/viewer?url=${ chat.msg.url}&embedded=true | safe:'resourceUrl'" frameborder="0"></iframe>;
					//msg = `<a class="attach" href="${chat.msg.url}" target="_blank"><iframe src="https://docs.google.com/viewerng/viewer?url=${chat.msg.url}&embedded=true" frameborder="0"></iframe></a><p style="color:#fff;">${chat.msg.name}</p>`;
					msg = `<a class="file" href="${chat.msg.url}" target="_blank">${chat.msg.name}</a>`;
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
					
					chat_messages += date_html;
				}
	
				if (chat.user_id != admin_user_id) {
	
					if (typeof friendPhoto === undefined) {
						
						chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_user"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
					} else {
						chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_user"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
						
					}
				} else {
					chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_admin"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
				}
				} else {
					sameChat = false;
				}
				
			});
			
			if(chat_messages === "" && currentUserKey !== "") {
				sendMessageFromAdmin(chatkey,chatkey,false);
				LoadChatMessages(chatkey,chatkey);
			}
			if(sameChat) {
			
			}
			
			
			$('#chat_converse').html(chat_messages);

			//$('#chat_converse').append('<span class="chat_msg_item chat_msg_item_user raoircle"></span>');
			document.getElementById('chat_converse').scrollTo(0, document.getElementById('chat_converse').scrollHeight);	
			
		});
	}
	else {
	chat_db.once('value', function (chats) {
		var chat_messages = "";
		var today_date = moment().format("YYYY-MM-DD");
		var yesterday_date = moment().subtract(1,'days').format("YYYY-MM-DD");
		var date_loop_array = [];
		
		chats.forEach(function (data) {
			var chat = data.val();
			var li_id = data.key;
			var date_format = moment(chat.date).format("YYYY-MM-DD");
			var time_format = moment(chat.date).format('HH:mm:ss');
			var msg = '';
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
			
			
			var currentFriendKey = localStorage.getItem('currentFriendKey')

			if (currentFriendKey === friendKey ) {
				sameChat = true;
				
			
			if (chat.message_type === 'image') {
				msg = `<a href="${chat.msg.url}" target="_blank"><img src='${chat.msg.url}' class="img-fluid attach" /></a>`;
			}
			else if (chat.message_type === 'audio') {
				
				msg = `<audio controls>
						<source src="${chat.msg.url}" type="audio/webm" />
					</audio>`;
			} else if (chat.message_type === 'latlon') {
				msg = `<iframe src="https://maps.google.com/maps?q=${chat.msg.lat}, ${chat.msg.lon}&z=15&output=embed" width="360" height="270" frameborder="0" style="border:0"></iframe>`;
			} else if (chat.message_type === 'file') {
				//msg = `<iframe src="https://docs.google.com/viewerng/viewer?url=${ chat.msg.url}&embedded=true | safe:'resourceUrl'" frameborder="0"></iframe>;
				//msg = `<a class="attach" href="${chat.msg.url}" target="_blank"><iframe src="https://docs.google.com/viewerng/viewer?url=${chat.msg.url}&embedded=true" frameborder="0"></iframe></a><p style="color:#fff;">${chat.msg.name}</p>`;
				msg = `<a class="file" href="${chat.msg.url}" target="_blank">${chat.msg.name}</a>`;
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
				
				chat_messages += date_html;
			}

			if (chat.user_id != admin_user_id) {

				if (typeof friendPhoto === undefined) {
					
					chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_user"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
				} else {
					chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_user"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
					
				}
			} else {
				chat_messages += `<span id="`+li_id+`" class="chat_msg_item chat_msg_item_admin"><span class="msg">${msg}</span><span class="time_status">${time_format}</span></span>`;
			}
			} else {
				sameChat = false;
			}
			
		});
		
		
		if(chat_messages === "" && currentUserKey !== "") {
			sendMessageFromAdmin(chatkey,chatkey,false);
			LoadChatMessages(chatkey,chatkey);
		}
		if(sameChat) {
		
		}
		
		if(chat_messages !== "")
		{
			$('.raocircle').remove();
			document.getElementById('chat_converse').html = chat_messages;
		
		}
		//$('#chat_converse').append('<span class="chat_msg_item chat_msg_item_user raoircle"></span>');
		document.getElementById('chat_converse').scrollTo(0, document.getElementById('chat_converse').scrollHeight);	
		
	});

	}
}

function render_chat_messages() {

}


function sendMessageFromAdmin(chatKey,friendKey,$anonym) {
	
	var chat_friend_id = $("#current_admin_id").val();
	var chat_currentUserId = 1;
	var message_text = siteConfig.welcome_message;
	if(is_new_session) {
		is_new_session = false;
		Cookies.set('rcfv_chat_session',"yes");
		message_text = message_text + session_html;
	}
	var msgData = {
        date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
        is_read: 0,
        message_type: 'text',
        msg: message_text,
        user_id: chat_currentUserId,
        sender_id: chat_currentUserId,
    }
	
    var message = firebase.database().ref('chat_data/' + chatKey + '/message').push()
    message.set(msgData);
	
	firebase.database().ref('fcmTokens').child(chatKey).once('value').then(function (data) {
		
    });
	
	attach_thread(chatKey,chat_friend_id);
	LoadChatMessages(chatKey,friendKey,$anonym);
	
	
}


function SendMessage() {
	var status = true;
	if(undefined === firebase || undefined === firebase.database)
		{
			status = false;
			
		}
		update_status(status);
		if(!status)
		return;
	
	var friend_id = jQuery('#current_role_id').val();
    var currentUserId = jQuery('#current_id').val();
	var text_message = document.getElementById('chatSend').value;
	
	if(text_message == "")
	return false;

	if(is_new_session) {
		is_new_session = false;
		Cookies.set('rcfv_chat_session',"yes");
		text_message = text_message + session_html;
	}
	text_message =  text_message.replace(/\n$/, "");
    var msgData = {
        date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
        is_read: 0,
        message_type: 'text',
        msg: text_message,
        user_id: currentUserId,
        sender_id: currentUserId,
    }
	

    var message = firebase.database().ref('chat_data/' + friend_id + '/message').push()
    message.set(msgData);
	if(device_token.length > 0)
    send_notification("New message received",text_message,currentUserId);
	var admin_id = $('#current_admin_id').val();

	attach_thread(friend_id,admin_id);
	
   // document.getElementById('txtMessage').value = '';
    //document.getElementById('txtMessage').focus();
    //         }
    //     });
}

function send_notification(body,text_message,currentUserId) {
	$.ajax({
		url: 'https://fcm.googleapis.com/fcm/send',
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			//                         'Authorization': 'key=AAAAnxpAQzs:APA91bGrhqw9en3CoEOoC59kzUQ8y-JsawST__K03bAaaiLxfPyyr5cgITEl-2GvBJTM3r_WfAnbe1cxETcntsVAB-H37DNdK8tF3fCiF8tfNvdzWIiqWKdOvIpMyV73u8vdgVWUdYtS'
		   'Authorization': 'key=AAAArNNeNDs:APA91bFlIKEF2M6uGbFhrwA8bqQdlmWlW-HWg1xinK0ClMRwdJl7-7VQqacpZbJ6HfYCZBost4QCj7pmfD4Jkd2rXOxHQIBudBG1jR61OvjeSs8xe17HHZXmSDd2Bd4NdkaF3-SOyjmn'
		},
		
		data: JSON.stringify({
			"registration_ids": device_token,
			"notification": {
			  "body": body,
			  "content_available": true,
			  "priority": "high",
			  "title": text_message.substring(0, 30)
			},
			"data": {
			  "priority": "high",
			  "sound": "default",
			  "content_available": true,
			  "bodyText": currentUserId, 
			  
			}
		  }),
		success: function (response) {
			console.log(response);
		},
		error: function (xhr, status, error) {
			console.log(xhr.error);
		}
	});
}

function attach_thread(current_chat_id,admin_id) {
	
	var friendList = { friendId: current_chat_id, userId: admin_id};
	
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
	if(false === flag) {
		chatKey = firebase.database().ref('friend_list').push(friendList, function (error) {
			if (error) alert(error);
		}).getKey();
	}
	});
	

}

// Load All Emoji
function loadAllEmoji() {
	var emoji = '';
	for (var i = 128512; i <= 128566; i++) {
		emoji += `<a href="#" style="font-size: 22px;" onclick="getEmoji(this)">&#${i};</a>`;
	}
	document.getElementById('smiley').innerHTML = emoji;
}


function showEmojiPanel() {
	document.getElementById('emoji').removeAttribute('style');
}

function hideEmojiPanel() {
	document.getElementById('emoji').setAttribute('style', 'display:none;');
}

function getEmoji(control) {
	document.getElementById('txtMessage').value += control.innerHTML;
}

function update_status(status) {
	var saved_status = siteConfig.error_status;
	if(!status) {
		alert("Chat widget not working due to technical issue");
		status = "no";
	} else {
		status = "yes";
	}
	if(status === saved_status)
	{
		//do nothing
		
	} else {
		$.ajax({
			url: siteConfig.ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: {
				action: "update_error_display",
				type: "POST",
				"error_display": status
			},
			success: function(response) {
				siteConfig.error_status = status;
				
			}
		});
	}
}

function get_message_html(msg, message_type) {
	if (message_type === 'image') {
		msg = `<a href="${msg.url}" target="_blank"><img src='${msg.url}' class="img-fluid attach" /></a>`;
	}
	else if (message_type === 'audio') {
		
		msg = `<audio controls>
				<source src="${msg.url}" type="audio/webm" />
			</audio>`;
	} else if (message_type === 'latlon') {
		msg = `<iframe src="https://maps.google.com/maps?q=${msg.lat}, ${msg.lon}&z=15&output=embed" width="360" height="270" frameborder="0" style="border:0"></iframe>`;
	} else if (message_type === 'file') {
		//msg = `<iframe src="https://docs.google.com/viewerng/viewer?url=${ chat.msg.url}&embedded=true | safe:'resourceUrl'" frameborder="0"></iframe>;
		//msg = `<a class="attach" href="${chat.msg.url}" target="_blank"><iframe src="https://docs.google.com/viewerng/viewer?url=${chat.msg.url}&embedded=true" frameborder="0"></iframe></a><p style="color:#fff;">${chat.msg.name}</p>`;
		msg = `<a class="file" href="${msg.url}" target="_blank">${msg.name}</a>`;
	} else if (message_type === 'video') {
		msg = `<video controls width="360" height="270">
				   <source src="${msg.url}" type="video/mp4" />
			   </video>`;
	}
	else {
		msg = msg;
	}

	return msg;
}

function get_admin_message_html() {

}
});

function SendImage(event) {
	var status = true;
	/*if(undefined === firebase || undefined === firebase.database)
		{
			status = false;
			
		}
		update_status(status);
		if(!status)
		return;*/

		var file = event.files[0];
		
	jQuery('#chat_converse').append('<span class="chat_msg_item chat_msg_item_user raocircle"></span>');
	document.getElementById('chat_converse').scrollTo(0, document.getElementById('chat_converse').scrollHeight);	
    const ref = firebase.storage().ref();
    var file = event.files[0];
	var friend_id = jQuery('#current_role_id').val();
    const lastDot = file.name.lastIndexOf('.');
    const ext = file.name.substring(lastDot + 1);
    var currentUserId = jQuery('#current_id').val();
    const metadata = {
        contentType: file.type
    };
    const name = (+new Date()) + '-' + file.name;
    const task = ref.child(name).put(file, metadata);
	
    if (!file.type.match("image.*")) {
		
        message_type = 'file';
    }
    else {
		message_type = 'image';
	}
	
        

        task
            .then(snapshot => snapshot.ref.getDownloadURL())
            .then((url) => {
                var msgData = {
                    date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
                    is_read: 0,
                    message_type: message_type,
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
                        //document.getElementById('txtMessage').value = '';
                        //document.getElementById('txtMessage').focus();
						if(device_token.length > 0)
    					send_notification("New "+message_type+" received",name,currentUserId);
                    }
                });
            })
            .catch((error)=>{
				//$('.raocircle').remove();
				var text_message = '<span style="color:red;padding:5px;background:#fff;">Could not upload document, We shall get back to you shortly!</span>';
				var msgData = {
					date: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
					is_read: 0,
					message_type: 'text',
					msg: text_message,
					user_id: currentUserId,
					sender_id: currentUserId,
				}
				var message = firebase.database().ref('chat_data/' + friend_id + '/message').push()
				message.set(msgData);
				if(device_token.length > 0)
				send_notification("New message received",text_message,currentUserId);
			});

		
}