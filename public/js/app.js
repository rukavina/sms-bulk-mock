

var app = {
    ws: null,
    connect: function(wsurl){
        var self = this;
        this.ws = new WebSocket(wsurl);
        this.ws.onopen = function(e){
            console.log("Connection established!");
            $('#mocksection').hide();
            $('#requestsection').show();
            toastr.success('WS Mock server connected!');
        };
        
        this.ws.onclose = function(e){
            console.log("Connection closed!");
            $('#mocksection').show();
            $('#requestsection').hide();
            toastr.error('WS Mock server disconnected');
        };
        
        this.ws.onerror = function(e){
            console.log("Connection error!");
            $('#mocksection').show();
            $('#requestsection').hide();
            toastr.error('WS Mock server error');
        };        

        this.ws.onmessage = function(m){
            var msg = JSON.parse(m.data);
            self.logLine('Message type received: ' + msg.type);
            switch (msg.type){
                case 'error':
                    toastr.error(msg.data.message);
                    break;
                case 'bulk_msg':
                    toastr.success('BULK success');
                    $('#received').prepend('<tr><td>' + msg.data.sender + '</td><td>' + msg.data.receiver + '</td><td>' + self.nl2br(msg.data.text) + '</td></tr>');
                    self.logData(msg.data);
                    break;                    
            }                        
            self.logLine('===================');
        };      
    },
    sendMessage: function (type, data){
        var message = JSON.stringify({
            'type': type,
            'data': data
        });
        this.ws.send(message);
    },
    
    init: function(){
        var self = this;
        $('#connectBtn').click(function(){
            self.connect($('#wsurl').val());
        });       
    },
    logLine: function(line){
        console.log(line);
        $('#log').val($('#log').val() + "\n" + line);
        var textarea = document.getElementById('log');
        textarea.scrollTop = textarea.scrollHeight;
    },
    logData: function(data){
        console.log(data);
        var self = this;
        for(var key in data){
            self.logLine(key + ": " + data[key]);
        };        
    },
    nl2br: function(text){
        return text.replace(/(?:\r\n|\r|\n)/g, '<br>');
    }    
    
};
        


