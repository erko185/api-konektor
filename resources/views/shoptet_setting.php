<!DOCTYPE html>
<html>
<head>
    <title>Nastavenia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            top: 30px;
            font-size: 17px;
        }
        .success{
            background:#04aa6d
        }
        .alert{
            background: red;
        }

        #snackbar.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @-webkit-keyframes fadein {
            from {top: 0; opacity: 0;}
            to {top: 30px; opacity: 1;}
        }

        @keyframes fadein {
            from {top: 0; opacity: 0;}
            to {top: 30px; opacity: 1;}
        }

        @-webkit-keyframes fadeout {
            from {top: 30px; opacity: 1;}
            to {top: 0; opacity: 0;}
        }

        @keyframes fadeout {
            from {top: 30px; opacity: 1;}
            to {top: 0; opacity: 0;}
        }
    </style>

</head>
<body>
<div class="container">
    <div class="insert"></div>
    <div class="row" style="margin-top:20px">

        <?php  if($access){?>
        <div class="col-md-6" style="margin: auto">
            <div>
                <h1>
                    Depo.sk -nastavenia ku Shoptetu
                </h1>
            </div>
            <form id='data' action="<?php echo  'https://'.$_SERVER['HTTP_HOST']?>/public/password" method="post">
                <input type="hidden"  name="eshop_id" value="<?php echo $_GET["eshopId"] ?>">
                <div class="form-group">
                    <label for="email">E-mailova adresa</label>
                    <input value="<?php echo $name ?>" type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                           placeholder="Vložte email">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Heslo</label>
                    <input  value="<?php echo $password ?>" type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Vložte heslo">
                </div>
                <div class="form-check">
                    <input type="checkbox" <?php if($address!="") echo 'checked'?> name="address" class="form-check-input" id="pick">
                    <label class="form-check-label" for="exampleCheck1">Vyzdvihnúť z adresy klienta</label>
                </div>

                <button  type="submit" class="btn btn-primary">Uložiť</button>
            </form>
        </div>
        <?php } else echo "false"?>
    </div>
</div>
<div id="snackbar"></div>
<script>
    $(".insert").append("<iframe src=\"https://admin.depo.sk/eshop?c=1&o=unikatne_cislo_objednavky\" width=\"600\" height=\"600\" frameborder=\"0\"></iframe>\n");

    $("#data").submit(function(e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // serializes the form's elements.
            success: function(data)
            {
                if(data=='success'){
                    $("#snackbar").empty().append("Data sa úspešne uložili")
                    $("#snackbar").attr("style",'background:#04aa6d')
                    var x = document.getElementById("snackbar");
                    x.className = "show success";
                    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
                }
                else if(data=='wrong data'){
                    $("#snackbar").empty().append("Nespravné prihlasovacie údaje")
                    $("#snackbar").attr("style",'background:red')
                    var x = document.getElementById("snackbar");
                    x.className = "show alert";
                    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
                }
                else{
                    $("#snackbar").empty().append("Data sa neúspešne uložili")
                    $("#snackbar").attr("style",'background:red')
                    var x = document.getElementById("snackbar");
                    x.className = "show alert";
                    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
                }

            }
        });


    });
</script>


</body>
</html>
