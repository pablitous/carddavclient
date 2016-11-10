 <div class="footer">
            <div class="pull-right">
                -
            </div>
            <div>
                <strong>Copyright</strong> <a href="http://www.ebsolutions.com.ar" target="_blank">Even Better Solutions</a> &copy; 2016
            </div>
        </div>

        </div>
        </div>

    <!-- Mainly scripts -->
    <script src="js/jquery-2.1.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="js/inspinia.js"></script>
    <script src="js/plugins/pace/pace.min.js"></script>
    <script type="text/javascript">
    paceOptions = {
      // Configuration goes here. Example:
      elements: false,
      restartOnPushState: false,
      restartOnRequestAfter: false
    }
    Pace.on("start", function(){
        $("div.paceDiv").show();
    });

    Pace.on("done", function(){
        $("div.paceDiv").hide();
    });
    $(document).on("keypress", "form", function(event) { 
        return event.keyCode != 13;
    });

    </script>
</body>
</html>
