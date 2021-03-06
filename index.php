<!DOCTYPE html>
<html>
    <head>
        <title>Scraping demo</title>
        <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.css"/>
        <link rel="stylesheet" href="dist/css/formValidation.css"/>

        <script type="text/javascript" src="vendor/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="dist/js/formValidation.js"></script>
        <script type="text/javascript" src="dist/js/framework/bootstrap.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="page-header">
                        <h2>Scraping Form</h2>
                    </div>

                    <form id="defaultForm" method="post" class="form-horizontal" action="target.php">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Business Name</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="businessName" id="businessName" placeholder="Business Name" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Address</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="address" id="address" placeholder="Address" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">City</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="city" id="city" placeholder="City" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">State</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="state" id="state" placeholder="State" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Zip</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="zip" id="zip" placeholder="Zip" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="country" id="country" placeholder="country" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phone Number</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="phoneNo" id="phoneNo" placeholder="Phone No" />
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary" name="scan" id="scan" value="Scan Now">Scan Now</button>
                            </div>
                        </div>
                    </form>
                    <div id="loader"></div>
                    <div id="result">
                        <div id="errormsg"></div>
                        <div id="resultHTML"></div>
                        <!-- 
                        <div id="bname"></div>
                        <div id="add"></div>
                        <div id="pnum"></div>
                        -->
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {

                $('#defaultForm').formValidation({
                    message: 'This value is not valid',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        businessName: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The business name is required'
                                }
                            }
                        },
                        address: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The address is required'
                                }
                            }
                        },
                        city: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The city is required'
                                }
                            }
                        },
                        state: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The state is required'
                                }
                            }
                        },
                        zip: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The zip is required'
                                }
                            }
                        },
                        country: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The country is required'
                                }
                            }
                        },
                        phoneNo: {
                            row: '.col-sm-5',
                            validators: {
                                notEmpty: {
                                    message: 'The phone no is required'
                                }
                            }
                        }
                    }
                })
                    .on('success.form.fv', function(e) {
                    $("#loader").show();
                    $("#result").hide();
                    // Prevent form submission
                    e.preventDefault();

                    // Get the form instance
                    var $form = $(e.target);

                    // Get the FormValidation instance
                    var bv = $form.data('formValidation');

                    // Use Ajax to submit form data
                    $.post($form.attr('action'), $form.serialize(), function(result) {
                        $("#loader").hide();
                        $("#result").show();
                        $("#errormsg").empty();
                        $("#resultHTML").empty();
                        if (result.errorMsg) {
                            $("#errormsg").html(result.errorMsg);
                        }
                        else {
                            $("#resultHTML").html(result.HTML);
                        }
                    }, 'json');
                });
            });
        </script>
        <style>
            #result{
                background-color: #F3F3F3;
                padding: 15px;
                border: solid 1px#ddd;
                display: none;
            }
            #bname{
                font-weight: bold;
                margin-bottom: 5px;
            }
            #add{
                margin-bottom: 5px;
            }
            #loader {
                background-image: url("dist/img/loading.gif");
                height: 100px;
                margin-left: 28%;
                position: absolute;
                width: 100px;
                display:none;
            }
        </style>
    </body>
</html>