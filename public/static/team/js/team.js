const joinBtn = document.getElementById("join-btn");
const team = CURRENT_TEAM

if(joinBtn) {
    const url = joinBtn.getAttribute("data-join");

    joinBtn.addEventListener("click", (e) => {
        let joinWindow = new PoppupWindow({
            title: "Join the team",
            textInfo: "Do you really want to join this team?",
            
            
            agreeEvent: async () => {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        team: team,
                        csrfmiddlewaretoken: window.CSRF_TOKEN
                    },

                    success: function (data) {
                        switch (data.type) {
                            case "Successful":
                                sendNotification(data.message, NotificationType.success)
                                break;
                        
                            default:
                                sendNotification(data.message, NotificationType.note)
                                break;
                        }
                    },
                    error: function (jqXHR, exception) {
                        sendNotification(jqXHR.responseText, NotificationType.error)
                    }
                    
                });
            }
        }).show();
    })
    
}
