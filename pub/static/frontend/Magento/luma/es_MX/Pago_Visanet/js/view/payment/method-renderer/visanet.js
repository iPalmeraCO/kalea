define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate',
         'jquery'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t,$) {

        $(document).on('change',"[name='payment[vcc]']",function(){
            var valor = $("#visanet_vcc").val();
            var total = $(".price").html();
            var precio = total.split("Q.");            
            precio = parseFloat(precio[1].trim().replace(',', ''));            
            var cuotas = valor.split("VC");            
            cuotas = parseInt(cuotas[1]);
           
            if (valor == "VC12" || valor == "VC15" || valor == "VC18"){
                //Sumar 10% de recargo
                precio = (precio * 0.07) + precio;
                
            } 
            var valorcuota = (precio/cuotas);
            var cuotastring = $("#visanet_vcc option:selected").text(); 
            $("#ncuotas").html(cuotastring);
            $("#vcuotas").html("Q. "+parseFloat(valorcuota.toFixed(2)).toLocaleString("es-GT",{minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $("#tpagar").html("Q. "+parseFloat(precio.toFixed(2)).toLocaleString("es-GT", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $(".valorespago").show();
            //var html = "Número de cuotas :"+cuotastring+" \n Total a pagar:"+precio+" \n Valor cuotas: "+valorcuota;
            //alert(html);


        });

        return Component.extend({
            defaults: {
                template: 'Pago_Visanet/payment/visanet',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: '',
                selectedCardType: null
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType' 
                    ]);
                return this;
            },

            initialize: function() {
                var self = this;
                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function(value) {
                    var result;
                    self.selectedCardType(null);

                    if (value == '' || value == null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                    }
                });

                /*this.vname.subscribe(function(value) {
                    name = value;
                    console.log(value);
                });*/

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function(value) {
                    creditCardData.expirationYear = value;                    
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function(value) {
                    creditCardData.expirationYear = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function(value) {
                    creditCardData.cvvCode = value;
                });
            },

            getCode: function() {
                return 'visanet';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'vname': $("#visanet_vname").val(),
                        'lastname': $("#visanet_lastname").val(),
                        'vcc': $("#visanet_vcc").val(),
                    }
                };
            },

            /*validate: function(){
                var isvalid = false;

                if ($("#visanet_cc_number").val != ""){
                    isValid=true;
                }          

                if (isvalid == false){
                    alert("Verifica los datos");
                }
                return true;
            },*/
             isActive: function () {
                return true;
            },
            getCcAvailableTypes: function() {
                return window.checkoutConfig.payment.visanet.availableTypes[this.getCode()];
            },
            getIcons: function (type) {
                return window.checkoutConfig.payment.visanet.icons.hasOwnProperty(type)
                    ? window.checkoutConfig.payment.visanet.icons[type]
                    : false
            },
            getCcMonths: function() {
                return window.checkoutConfig.payment.visanet.months[this.getCode()];
            },
            getCcYears: function() {
                return window.checkoutConfig.payment.visanet.years[this.getCode()];
            },
            hasVerification: function() {
                return window.checkoutConfig.payment.visanet.hasVerification[this.getCode()];
            },
            hasSsCardType: function() {
                return window.checkoutConfig.payment.visanet.hasSsCardType[this.getCode()];
            },
            getCvvImageUrl: function() {
                return window.checkoutConfig.payment.visanet.cvvImageUrl[this.getCode()];
            },
            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            },
            getSsStartYears: function() {
                return window.checkoutConfig.payment.visanet.ssStartYears[this.getCode()];
            },
            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            getSsStartYearsValues: function() {
                return _.map(this.getSsStartYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function() {
                return false;
            },
            getCcTypeTitleByCode: function(code) {
                var title = '';
                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function(number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function() {
                return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            }            
        });
    }
);