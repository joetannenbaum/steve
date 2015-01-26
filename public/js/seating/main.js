$(function() {

    loadSeating();

    $("#guest-list li").draggable({
      connectToSortable: ".available ul",
      helper: "clone",
      revert: "invalid",
      drag: function() {
        $("#tables").addClass("dragging");
      }
    });

    $(".table ul").sortable({
      revert: true,
      connectWith: ".available ul",
      update: function(event, ui) {
        updateGuestList($(ui.item), "remove");
        updateTables();
      }
    });

    $("#tables")
        .on("click", ".remove", function() {
            var $li = $(this).closest("li");

            updateGuestList($li, "add");
            $li.remove();
            updateTables();

            return false;
        });

    $("#toggle-save").click(function() {
        $(this).fadeOut(function() {
            $("#save-form").fadeIn();
        });

        return false;
    });

    $("#add-person-submit").click(function() {
        $.ajax({
            url: "/sit-down/add-person",
            type: "post",
            dataType: "json",
            data: {
                name: $("#person-name").val()
            },
            success: function(r) {
                var $li = $("<li />").html(r.html).attr("data-guest", r.id);

                $li.appendTo("#guest-list");

                $("#guest-list li").draggable({
                  connectToSortable: ".available ul",
                  helper: "clone",
                  revert: "invalid",
                  drag: function() {
                    $("#tables").addClass("dragging");
                  }
                });

                $("#add-person-form").fadeOut(function() {
                    $(this).find("input").val("");
                    $("#add-person-success").fadeIn().delay(3000).fadeOut(function() {
                        $("#add-person-form").fadeIn();
                    });
                });
            }
        });

        return false;
    });

    $("#save-submit").click(function() {

        var tables = {};

        $(".table").each(function() {
            var tableID =  $(this).data("table");

            tables[ tableID ] = [];

            $(this).find("ul").children().each(function() {
                tables[ tableID ].push($(this).data("guest"));
            });
        });

        $.ajax({
            url: "/sit-down",
            type: "post",
            dataType: "json",
            data: {
                name: $("#save-title").val(),
                arrangement: tables
            },
            success: function(r) {
                var $li = $("<li />").html(r.html);

                $li.prependTo("#all-seating");

                $("#save-form").fadeOut(function() {
                    $(this).find("input").val("");
                    $("#save-success").fadeIn().delay(3000).fadeOut(function() {
                        $("#toggle-save").fadeIn();
                    });
                });
            }
        });

        return false;
    });
});

function loadSeating() {
  if (seating) {
    for (s in seating) {
      for(var i = 0; i < seating[s].length; i++) {
        var $guest = $("#guest-list").find("li[data-guest='" + seating[s][i] + "']");
        $guest.clone().appendTo($("#table-" + s).find("ul"));
        updateGuestList($guest, "remove");
      }
    }

    updateTables();
  }
}

function updateTables() {
    $(".table").each(function() {
        var max = $(this).data("max-people"),
            people = $(this).find("ul").children().size();

        $(this).find(".count").text(people);

        if (people < max) {
            $(this).addClass("available");
        } else {
            $(this).removeClass("available");
        }

        $("#tables").removeClass("dragging");
    });
}

function updateGuestList($item, action) {
    var guest = $item.data("guest");

    switch(action) {
        case "remove":
            $("#guest-list").find("li[data-guest='" + guest + "']").hide();
        break;

        case "add":
            $("#guest-list").find("li[data-guest='" + guest + "']").show();
        break;
    }
}
