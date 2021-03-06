jQuery(document).ready(function(a) {
    var f = mute.stop,
        g = mute.start,
        e = mute.ajax_url;
    a(document).on("click", ".muted", function() {
        var b = a(this).attr("id"),
            c = b.split("-")[1];
        a.ajax({
            type: "POST",
            dataType: "json",
            url: e,
            data: {
                action: "unmute",
                stop: f,
                uid: c
            },
            success: function(d) {
                "failure" !== d.status && (a("#" + b).removeClass("muted").addClass("unmuted").attr("title", "Mute").attr("id", "mute-" + c).html("Mute"), a("a#user-mute span.count").html(d.count))
            },
            error: function(a) {}
        });
        return !1
    });
    a(document).on("click", ".unmuted",
        function() {
            var b = a(this).attr("id"),
                c = b.split("-")[1];
            a.ajax({
                type: "POST",
                url: e,
                data: {
                    action: "mute",
                    start: g,
                    uid: c
                },
                success: function(d) {
                    "failure" !== d.status && (a("#" + b).removeClass("unmuted").addClass("muted").attr("title", "Unmute").attr("id", "unmute-" + c).html("Unmute"), a("a#user-mute span.count").html(d.count))
                },
                error: function(a) {}
            });
            return !1
        })
});