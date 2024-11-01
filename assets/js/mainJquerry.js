jQuery(document).ready(
    function($){
        $(".sAppchangelog_title").on("click",
            function(){
                var id = parseInt($(this).attr('id').match(/schangelogtitle-(\d+)/)[1],10);
                var boddyID = '#boddyChangelog_'+id;
                $(boddyID).toggle("slow");
            }
        );
    }
);

