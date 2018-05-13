(function (window, document, $, evoShopConfig) {
    var evoShop = evoShop || {};

    evoShop.cart = JSON.parse(localStorage.getItem('evoShop')) || {};
    evoShop.ajaxProgress = false;
    evoShopConfig = evoShopConfig || {};
    
    var typeof_string           = typeof "",
        typeof_undefined        = typeof undefined,
        typeof_number           = typeof 0,
        typeof_function         = typeof function () {},
        typeof_object           = typeof {},
        isTypeOf                = function (item, type) { return typeof item === type; },
        isNumber                = function (item) { return isTypeOf(item, typeof_number); },
        isString                = function (item) { return isTypeOf(item, typeof_string); },
        isUndefined             = function (item) { return isTypeOf(item, typeof_undefined); },
        isFunction              = function (item) { return isTypeOf(item, typeof_function); },
        isObject                = function (item) { return isTypeOf(item, typeof_object); },

        isElement               = function (o) {
            return typeof HTMLElement === "object" ? o instanceof HTMLElement : typeof o === "object" && o.nodeType === 1 && typeof o.nodeName === "string";
        },

        isInt = function (value) {
          return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value))
        };


////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// TRIGGERS ///////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

        evoShop.triggers = {
            beforeAdd: function(params){
                var items = params.items;

                var def = $.Deferred();
                evoShop.debug('trigger BEFORE ADD input: ', items);

                //Запускаем пользовательскую функцию, если она существует
                if(isFunction(evoShop.beforeAdd)) {
                    evoShop.debug('start evoShop.beforeAdd()');
                    evoShop.beforeAdd(items, function(result){
                        evoShop.debug('stop evoShop.beforeAdd()');
                        def.resolve(result);
                    });
                } else {
                    def.resolve(items);
                }

                return def.promise();
            },

            afterAdd: function(params){
                var items = params.items;
                var def = $.Deferred();
                evoShop.debug('trigger AFTER ADD cart: ');  


                    if(evoShopConfig.settings.showPopupAfterAdd) {
                        evoShop.generateHelper(evoShopConfig.templates.addedPopup, function(){});
                        def.resolve(items);
                    }

                    //Запускаем пользовательскую функцию
                    if(isFunction(evoShop.afterAdd)) {
                        evoShop.debug('start evoShop.afterAdd()');
                        evoShop.afterAdd(items, function(result){
                           evoShop.debug('stop evoShop.afterAdd()'); 
                           def.resolve(result);
                        });        
                    } else {
                        def.resolve(items);
                    }

                return def.promise();
            },

            beforeRemove: function(params) {
                var element = $('[data-hash="'+params.hash+'"]');
                var item = params.item;
                var def = $.Deferred();
                evoShop.debug('trigger BEFORE DELETE cart: ', item);

                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.beforeRemove)) {
                    evoShop.debug('start evoShop.beforeRemove()');
                    evoShop.beforeRemove(element, item, function(){
                        evoShop.debug('stop evoShop.beforeRemove()');
                        def.resolve(params.hash);
                    });
                } else {
                    def.resolve(params.hash);
                }

                return def;
            },

            afterRemove: function(params) {
                var item = params.item;
                var element = $('[data-hash="'+params.hash+'"]');
                var def = $.Deferred();
                evoShop.debug('trigger AFTER DELETE item: ', item);

                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.afterRemove)) {
                    evoShop.debug('start evoShop.afterRemove()');
                    evoShop.afterRemove(element, item, function(){
                        evoShop.debug('stop evoShop.afterRemove()');
                        def.resolve(item);
                    }); 
                } else {
                    def.resolve(item);
                }
                return def;
            },

            beforeClean: function() {
                var def = $.Deferred();
                evoShop.debug('trigger BEFORE CLEAN cart');

                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.beforeClean)) {
                    evoShop.debug('start evoShop.beforeClean()');
                    evoShop.beforeClean(function(){
                        evoShop.debug('stop evoShop.beforeClean()');
                        def.resolve();
                    });
                } else {
                     def.resolve();
                }

                return def;
            },

            afterClean: function() {
                var def = $.Deferred();
                evoShop.debug('trigger AFTER CLEAR cart');
                $('.es-popup').remove();

                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.afterClean)) {
                    evoShop.debug('start evoShop.afterClean()');
                    evoShop.afterClean(function(){
                        evoShop.debug('stop evoShop.afterClean()');
                        def.resolve();
                    });
                } else {
                    def.resolve();
                }
                return def;
            },

            beforeChange: function(params) {
                var item = params.item;
                var def = $.Deferred();
                evoShop.debug('trigger BEFORE CHANGE: ', item);
                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.beforeChange)) {
                    evoShop.debug('start evoShop.beforeChange()');
                    evoShop.beforeChange(item, function(){
                        evoShop.debug('stop evoShop.beforeChange()');
                        def.resolve(item);
                    });
                } else {
                    def.resolve(item);
                }
                return def;
            },

            afterChange: function(params) {
                var item = params.item;
                var def = $.Deferred();
                evoShop.debug('trigger AFTER CHANGE item: ', item);
                //Запускаем пользовательскую функцию
                if(isFunction(evoShop.afterChange)) {
                    evoShop.debug('start evoShop.afterChange()');
                    evoShop.afterChange(item, function(){
                        evoShop.debug('stop evoShop.afterChange()');
                        def.resolve(item);
                    }); 
                } else {
                    def.resolve(item);
                }
                return def;
            },

        };

    


////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// ACTIONS ///////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

evoShop.actions = {
    init: function() {       
        if(evoShopConfig) {
            evoShop.default();     
            //console.log(evoShopConfig);
            if(evoShopConfig.update) {
                return evoShop.chain('Update', {});
            }
        }
    },

    clean: function(callback) {
        var params = {};
        return evoShop.chain('Clean', params);
    },

    send: function(action, data){  
        var def = $.Deferred();        
        $.ajax({
            url: '/evoshop-ajax',
            type: 'post',
            dataType: 'json',
            data: {
                action:action, 
                data:data,
                lang:evoShopConfig.lang,
                currency:evoShopConfig.currency,
                cart_id:evoShop.getLS().cart_id
            },
            beforeSend: function(){
                evoShop.debug('ajax send ...');
                evoShop.ajaxProgress = true;

                if(isFunction(evoShop.beforeUpdate)) {
                    evoShop.debug('start evoShop.beforeUpdate()');
                    evoShop.beforeUpdate(function(){
                        evoShop.debug('stop evoShop.beforeUpdate()');
                    });
                }
            }
        }).fail(function() {
            evoShop.debug("ajax error");
            evoShop.ajaxProgress = false;
            def.reject('ajax error');
        }).done(function(result){
            if(result.status==200) {
                evoShop.cart = result.response;
                //evoShopConfig = result.config;
                if(result.response.cart_id) {
                    var ls = {'cart_id': result.response.cart_id, 'ids':result.response.ids};
                    evoShop.setLS(ls);
                } else {
                   evoShop.setLS('');
                }
            }
            def.resolve();
        }).always(function(){           
            evoShop.ajaxProgress = false;
            if(isFunction(evoShop.afterUpdate)) {
                evoShop.debug('start evoShop.afterUpdate()');
                evoShop.afterUpdate(function(){
                    evoShop.debug('stop evoShop.afterUpdate()');
                });
            }
        });

        return def.promise();
    },

    update: function () {
        if(!evoShop.cart.total_cnt) {
            $('.'+evoShopConfig.classes.miniCartClass).html(evoShopConfig.templates.emptyMiniCartTpl);
        } else {
            $('.'+evoShopConfig.classes.miniCartClass).html(evoShopConfig.templates.fillMiniCartTpl); 
        }

        $('.'+evoShopConfig.classes.totalSumClass).text(evoShop.cart.total_sum_formatted_sign);
        $('.'+evoShopConfig.classes.totalCntClass).text(evoShop.cart.total_cnt); 
        $('.'+evoShopConfig.classes.productPluralClass).text(evoShop.cart.pluralProduct); 

        if(evoShopConfig.settings.changeButtonText) {
            $('.'+evoShopConfig.classes.itemClass).find('.'+evoShopConfig.classes.buyBtnClass).text(evoShopConfig.buttonText.buttonTextDefault);
        }

        $('.'+evoShopConfig.classes.itemClass).removeClass(evoShopConfig.classes.addedClass);

        var html = '';
        if(evoShop.cart.total_cnt) {
            $.each(evoShop.cart.items, function (hash, item) {
                item.hash = hash;
                
                html += evoShop.nanoTPL(evoShopConfig.templates.fullCartRowTpl, item);
                //Изменяем текст кнопки у добавленных товаров
                if(evoShopConfig.settings.changeButtonText) {
                    $('.'+evoShopConfig.classes.itemClass+'-'+item.id).find('.'+evoShopConfig.classes.buyBtnClass).text(evoShopConfig.buttonText.buttonTextAfterAdd);
                }
                        
                //Добавляем класс `added` для всех элементов с только что добавленным id
                $('.'+evoShopConfig.classes.itemClass+'-'+item.id).addClass(evoShopConfig.classes.addedClass);

            });
        }
            
        $('.'+evoShopConfig.classes.fullCartClass).html(html);
        evoShop.disabledMinus();
        
    },

    add: function(items) {

        //Проверяем содержимое item

        //Если строка с id товаров, то формируем объект
        if (isString(items)) {
            arr = items.split(',');
            items = [];
            $.each(arr, function(i,val){
                items.push({id:Number(val.trim())});
            });
        }
        
        if(!isObject(items)) {
            evoShop.debug('items не является объектом', items);
            return false;
        }

        var error;
        $.each(items, function(i,item){
            if(!isNumber(item.id) || isNaN(item.id)){
                error = 'id не является числом';
                return false;
            } else if(item.quantity && !isNumber(item.quantity)){
                error = 'quantity не является числом';
                return false;
            } else if(item.options && !isObject(item.options)){
                error = 'options не является объектом';
                return false;
            }
        });

        if(!isUndefined(error)) {
            evoShop.debug(error, items);
            return false;
        }
        var params = {items:items};
        return evoShop.chain('Add', params); 
    },

    remove: function(hash) {
        var params = {hash:hash};
        return evoShop.chain('Remove', params);
    },

    change: function(hash, quantity) {
        var formItem = {};
        formItem.quantity = quantity;
        formItem.hash = hash;
        var params = {item:formItem};
        return evoShop.chain('Change', params);
    }
}



////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// HELPERS ///////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////


    evoShop.debug = function(msg, obj){
        if(evoShopConfig.settings.debug) {
            if(isObject(obj)) {
                console.log(msg, obj); 
            } else {
                console.log(msg);
            }   
        }  
    };

    // Получаем данные из LocalStorage
    evoShop.getLS = function(){
        if(localStorage.getItem('evoShop')) {
            return JSON.parse(localStorage.getItem('evoShop'));
        } else {
            return {'ids':[], 'cart_id':null};
        }
    };

    
    // Записываем данные в LocalStorage
    evoShop.setLS = function(o){
        localStorage.setItem('evoShop', JSON.stringify(o));
        return false;
    };

    //Получаем опции товара
    evoShop.getOptions = function(elm) {
        if(elm.is(":radio")){
            return elm.is(":checked") ? elm.val() : '';
        } else if(elm.is(":checkbox")){
            return elm.is(":checked") ? elm.val() : '';
        } else if (elm.is("[value], select")){
            return elm.val();
        } else if (elm.data("value") !== typeof undefined){
            return elm.data("value");
        } else {
            return elm.text();
        }
        return '';
    };

            
    evoShop.getCount = function(element) {

        var quantity = element.val() || "1";
        quantity = quantity.replace(',','.');
        quantity = !isNaN(Number(quantity)) ? Number(quantity) : 1;
        return quantity;
    };


    evoShop.getParams = function(itemContainer) {
        var p = {};
       
        var form = itemContainer.find('form');
        var params = $(form).serializeArray();
        $.each(params, function(i,value){
            if(p[value.name]) {
                if(isObject(p[value.name])) {
                    var arr = p[value.name];
                } else {
                    var arr = p[value.name].split(',');
                }

                arr.push(value.value); 
                p[value.name] = arr;
            } else {
                p[value.name] = value.value;
            }
        });

        return p;
    },

            
    evoShop.nanoTPL = function(template, data) {
                if(!template) return '';

                parseTpl = template.replace(/\{\+([\w\.]*)\+}/g, function(str, key) {
                    var keys = key.split('.'), value = data[keys.shift()];
                    [].slice.call(keys).forEach(function() {
                        value = value[this];
                    });
                    return (value === null || value === undefined) ? '' : value;
                });

        var row = $(parseTpl);
        $(row[0]).attr('data-hash', data.hash);
        $(row[0]).attr('data-step', data.step);
        //console.log(row);
        return $(row).prop('outerHTML');
    };

    evoShop.disabledMinus = function(){
        //Дизейблим кнопку "минус", если количество товара не может быть меньше 
        $('.'+evoShopConfig.classes.fullCartClass).find('[data-hash]').each(function(){
            var step = $(this).data('step') || 1;
            var input = $(this).find('[name="'+evoShopConfig.classes.quantityFieldName+'"]');
            var quantity = $(input).val();
            if(quantity<=step) {
                $(this).find('.'+evoShopConfig.classes.btnMinusClass).attr('disabled', true);
            } else {
                $(this).find('.'+evoShopConfig.classes.btnMinusClass).removeAttr('disabled');
            }
        });
    };

    evoShop.generateHelper = function(tpl, clickFn){
        $('.es-popup').remove();
        $('body').append(tpl).addClass('show');
        setTimeout(function(){
            $('.es-popup').addClass('show');
        },10); 
        $('.es-popup .confirm').on('click', clickFn);
    };


    evoShop.chain = function(name, params){
        if(this.ajaxProgress) {
            return false;
        } 
        var before = isFunction(evoShop.triggers['before'+name]) ? evoShop.triggers['before'+name] : function(){};
        var send = evoShop.actions.send;
        var after = isFunction(evoShop.triggers['after'+name]) ? evoShop.triggers['after'+name] : function(){};

        stepBefore = function(){
            $.when(before(params))
            .then(stepSend)
            .catch(function(mes){alert(mes);evoShop.actions.update;});
        }

        stepSend = function(){
            $.when(send(name, params))
            .then(stepAfter)
            .catch(function(mes){alert(mes);evoShop.actions.update;});
        }

        stepAfter = function(){
            $.when(after(params))
            .then(evoShop.actions.update)
            .catch(function(mes){alert(mes);});
        }

        stepBefore();
    };

    // reverse string function
    String.prototype.reverse = function () {
        return this.split("").reverse().join("");
    };

    evoShop.toCurrency = function (price) {
   
            if(isFunction(evoShop.priceFormat)) {
                return evoShop.priceFormat(price);
            }
            var num = parseFloat(price),
            opts = evoShopConfig.currencies[evoShopConfig.currency],

            numParts = num.toFixed(opts.accuracy).split("."),
            dec = numParts[1],            
            ints = numParts[0];                       
            
            ints = ints.reverse().match(new RegExp('.{1,' + 3 + '}','g')).join(opts.delimiter.reverse()).reverse();

            return  opts.prefix +
                    ints +
                    (dec ? opts.decimal + dec : "") +
                    opts.suffix;
    };

    evoShop.calculatePrice = function(itemContainer) {
        var options = $(itemContainer).find('input[data-price]:checked, select option:selected');
        var basePrice = $(itemContainer).find('form').data('baseprice') || 0;
        if(!basePrice) return;
        var outputPrice=Number(basePrice.toString().replace(',','.'));

        $.each(options, function(){
            var price=0;
            var changePrice = $(this).data('price').toString().replace(',','.');

            var symbol = changePrice.toString()[0];
            switch(symbol) {
                case '*':
                    var price = outputPrice * Number(changePrice.toString().slice(1));
                    outputPrice = price;
                break;
                case '+':
                    var price = Number(outputPrice) + Number(changePrice.toString().slice(1));
                    outputPrice = price;
                break;
                case '-':
                    var price = outputPrice - Number(changePrice.toString().slice(1));
                    outputPrice = price;
                break;
                default:
                    if(!symbol) {
                        var price = outputPrice;
                        outputPrice = price;
                    } else {
                        var price = Number(changePrice);
                        outputPrice = price;
                    }
                break;
            }
        });

        $(itemContainer).find('.'+evoShopConfig.classes.itemPriceClass).text(evoShop.toCurrency(outputPrice));
    };


////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// DEFAULT ////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
    evoShop.default = function(){
            $.each(evoShop.getLS().ids, function (i, id) {
                    //Изменяем текст кнопки у добавленных товаров
                    if(evoShopConfig.settings.changeButtonText) {
                        $('.'+evoShopConfig.classes.itemClass+'-'+id).find('.'+evoShopConfig.classes.buyBtnClass).text(evoShopConfig.buttonText.buttonTextAfterAdd);
                    }
                            
                    //Добавляем класс `added` для всех элементов с только что добавленным id
                    $('.'+evoShopConfig.classes.itemClass+'-'+id).addClass(evoShopConfig.classes.addedClass);
            });


        //Добавление единичного товара в корзину
        $(document).on('click', '.'+evoShopConfig.classes.buyBtnClass, function(e){  
            var itemContainer = $(this).closest('.'+evoShopConfig.classes.itemClass);
            var classList = itemContainer.attr('class').split(/\s+/);
            $.each(classList, function(i, val) {
                if (val.replace(evoShopConfig.classes.itemClass+'-', '') > 0) {
                   id  = Number(val.replace(evoShopConfig.classes.itemClass+'-', ''));
                }
            });

            if(!isNumber(id) || isNaN(id)) {
                evoShop.debug('не задан класс с id товара', classList);
                return false;
            }

            //Выбираем ВСЕ дочерние элементы .es-item
            var params = evoShop.getParams(itemContainer) || {};
            var img = itemContainer.find('.'+evoShopConfig.classes.imageClass).attr('src') || '';

            items = [{'id':id, 'quantity':1, 'img':img, 'options':params}];

            if(evoShopConfig.settings.showPopupBeforeAdd && itemContainer.find('[name="'+evoShopConfig.classes.quantityFieldName+'"]').length<1) {
                var clickFn = function(){
                    quantity = $('.es-popup').find('[name="'+evoShopConfig.classes.quantityFieldName+'"]').length ? evoShop.getCount($('.es-popup').find('[name="'+evoShopConfig.classes.quantityFieldName+'"]')) : evoShop.getCount(itemContainer.find('[name="'+evoShopConfig.classes.quantityFieldName+'"]'));
                    
                    items[0].quantity = Math.abs(quantity);
                    $('.es-popup').removeClass('show');
                    evoShop.actions.add(items);
                };

                var tpl = evoShopConfig.templates.cntPopup;
                var itemStep = itemContainer.find('[data-step]').data('step') || 1;
                
                itemStep = Number(itemStep.toString().replace(',','.'));
                if(itemStep>0) {
                    tpl = tpl.replace(/{\+step\+}/g, itemStep);
                }

                evoShop.generateHelper(tpl, clickFn);
                return;

            } else {
                quantity = evoShop.getCount(itemContainer.find('[name="'+evoShopConfig.classes.quantityFieldName+'"]'));
                items[0].quantity = Math.abs(quantity);
                evoShop.actions.add(items);
            } 
        });



        //Удаление товара из корзины
        $(document).on('click', '.'+evoShopConfig.classes.delBtnClass, function(e){
            e.preventDefault();
            var hash = $(this).closest('[data-hash]').data('hash');
            if(isString(hash) && hash.length>0) {
                if(evoShopConfig.settings.showPopupBeforeRemove) {
                   
                    var clickFn = function(){
                        $('.es-popup').removeClass('show');
                        evoShop.actions.remove(hash); 
                    };

                    return evoShop.generateHelper(evoShopConfig.templates.delPopup, clickFn);
                } else {
                    evoShop.actions.remove(hash);
                }
            } else {
                evoShop.debug('Отсутствует data-hash у кнопки удаления');
            }
        });

        //Очистка корзины
        $('#cleanCart').on('click', function(e){
            if(evoShopConfig.settings.showPopupBeforeClean) {
                var clickFn = function(){
                    $('.es-popup').removeClass('show');
                    evoShop.actions.clean(); 
                };
                return evoShop.generateHelper(evoShopConfig.templates.cleanPopup, clickFn);
            }
        });

        //Изменение цены в зависимости от выбранных параметров
        $('.options-group').find('select,input').on('change', function(){
            var itemContainer = $(this).closest('.'+evoShopConfig.classes.itemClass);
            evoShop.calculatePrice(itemContainer);
        });

        $('.'+evoShopConfig.classes.itemClass).each(function(){
            evoShop.calculatePrice(this); 
        });

        //Изменение кол-ва товара в корзине
        $(document).on('change', '.'+evoShopConfig.classes.fullCartClass+' [name="'+evoShopConfig.classes.quantityFieldName+'"]', function(){
            var hash = $(this).closest('[data-hash]').data('hash');
            evoShop.actions.change(hash, $(this).val());
        });
        
        $(document).on('click', '.'+evoShopConfig.classes.btnPlusClass, function(e){
            var hash = $(this).closest('[data-hash]').data('hash');
            var step = $(this).closest('[data-hash]').data('step') || 1;
            var input = $(this).closest('[data-hash]').find('[name="'+evoShopConfig.classes.quantityFieldName+'"]');
            $(input).val(($(input).val()*1000000 + step*1000000) / 1000000);
            evoShop.actions.change(hash, $(input).val());
        });

        evoShop.disabledMinus();  

        $(document).on('click', '.'+evoShopConfig.classes.btnMinusClass, function(e){
            var hash = $(this).closest('[data-hash]').data('hash');
            var step = $(this).closest('[data-hash]').data('step') || 1;
            var input = $(this).closest('[data-hash]').find('[name="'+evoShopConfig.classes.quantityFieldName+'"]');
            var newQuantity = ($(input).val()*1000000 - step*1000000) / 1000000;
            
            if(newQuantity<step) {
                $(this).attr('disabled', true);
                return false;
            } else {
                $(this).removeAttr('disabled');        
                $(input).val(newQuantity);
                evoShop.actions.change(hash, newQuantity);    
            }
            
        });


        $(document).on('click', '.closeBtn', function(e){
            e.preventDefault();
            $('.es-popup').removeClass('show');
        });
    }

    window.evoShop = evoShop;
    

    evoShop.actions.init();

    
})(window, document, jQuery, evoShopConfig);




//Пользовательские функции
evoShop.beforeUpdate = function(callback) {
    callback();
}

evoShop.afterUpdate = function(callback) {
    
    if($(evoShop.cart.items).length > 0) {
        //Показываем / скрываем fullCart
        $('#fullCart').show();
        $('#emptyCart').hide();
    } else {
        //Показываем / скрываем fullCart
        $('#fullCart').hide();
        $('#emptyCart').show();
    }

    callback();
}

evoShop.beforeAdd = function(items,callback) {
    $('.es-miniCart').css({'background': '#ccc'});
        //В колбеке можно вернуть модифицированный объект items
        callback();
}

evoShop.afterAdd = function(items, callback) {
    $('.es-miniCart').css({'background': '#fff'});
    //Колбек ничего не ожидает
    callback();
}

evoShop.beforeRemove = function(element, item, callback) {
    //element - элемент DOM, нажатой кнопки удаления
    $(element).fadeOut(300, function(){
        callback();
    });
}

/*evoShop.priceFormat = function(price) {
    return price;
}*/

/*evoShop.beforeChange = function(item, callback) {
    callback(item);
}*/

/*
evoShop.afterRemove = function(element, item, callback) {
    //element - элемент DOM, нажатой кнопки удаления
    callback();
}

evoShop.beforeClean = function(callback) {
    callback();
}

evoShop.afterClean = function(callback) {
    callback();
}
*/

//Самостоятельная обработка перед добавлением товара
$(document).on('click', '#testBtn', function(e){
    /*var items = [
        {'id':21, 'img':'/evoShop/img/superthumb.png'},
        {'id':22, 'img':'/evoShop/img/Minions-HD-Pictures-7.jpg'},
    ];*/
    evoShop.actions.add('21,20,22,23,24,25,26,27,28,29,30,31,32'); 
    //evoShop.actions.add(items); 
});



