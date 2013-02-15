<!DOCTYPE html>
<html>
    <head>
        <title>de/compress Themeroller-Parameter</title>
        <meta charset="utf-8" />
        <style>
        
			body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
			a, a:visited{text-decoration:none;color:blue;}
        </style>
    </head>
    <body> 
        <a href="../index.php?project=<?php echo $_GET['project'];?>">&lArr; back</a><hr />
        
        <table cellpadding="5" cellspacing="5">
        <tbody>
            <tr><td style="vertical-align: top;" align="left">
                <div style="white-space: nowrap;" class="lzmademo-title">
					<a href="https://github.com/nmrugg/LZMA-JS">LZMA.JS</a> based de/compression of JQueryUI-Theme-Parameters
				</div>
            </td></tr>
            <tr><td style="vertical-align: top;" align="left">
                <div class="lzmademo-description">
                    <p>put the compressed Parameter-String (#!zThemeParams=5d00000100800a00...) into the right Textarea and press Decompress</p>
                    <p>put the readable Parameter-String (ffDefault=...&amp;fwDefault=...) into the left Textarea and press Compress</p>
                </div>
            </td></tr>
        </tbody>
        </table>
        <table cellpadding="0" cellspacing="5">
            <tbody>
                <tr>
                    <td style="vertical-align: top;" align="left">
                        <table class="demo-panel" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td style="vertical-align: top;" align="left">
                                        <textarea rows="25" cols="70" class="gwt-TextArea" tabindex="0" id="left_text"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;" align="left">
                                        <table cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td style="vertical-align: top;" align="left">
                                                        
                                                    </td>
    
                                                    <td style="vertical-align: top;" align="left">
                                                        <button class="gwt-Button" type="button" tabindex="0" id="compress_button">Compress</button>
                                                    </td>
                                                    <td style="vertical-align: top;" align="left">
                                                        <button class="gwt-Button" type="button" tabindex="0" id="clear_left_button">Clear</button>
                                                    </td>
                                                    <td style="vertical-align: top;" align="left" id="left_output"></td>
                                                    <td style="vertical-align: top;" align="left" id="left_time"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
    
                    <td style="vertical-align: top;" align="left">
                        <table class="demo-panel" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td style="vertical-align: top;" align="left">
                                        <textarea rows="25" cols="70" class="gwt-TextArea" tabindex="0" id="right_text"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="vertical-align: top;" align="left">
                                        <table cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td style="vertical-align: top;" align="left">
                                                        <button class="gwt-Button" type="button" tabindex= "0" id="decompress_button">Decompress</button>
                                                    </td>
                                                    <td style="vertical-align: top;" align="left">
                                                        <button class="gwt-Button" type="button" tabindex="0" id="clear_right_button">Clear</button>
                                                    </td>
                                                    <td style="vertical-align: top;" align="left" id="right_output"></td>
                                                    <td style="vertical-align: top;" align="left" id="right_time"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <!--[if IE]>
        <script language="javascript" src="fix_ie_split.js"></script>
        <![endif]-->
        <script language="javascript" src="lzma.js"></script>
        <script>
            var clear_left_button_el  = document.getElementById("clear_left_button"),
                clear_right_button_el = document.getElementById("clear_right_button"),
                compress_button_el    = document.getElementById("compress_button"),
                decompress_button_el  = document.getElementById("decompress_button"),
                left_text_el          = document.getElementById("left_text"),
                left_output_el        = document.getElementById("left_output"),
                left_time_el          = document.getElementById("left_time"),
                right_text_el         = document.getElementById("right_text"),
                right_output_el       = document.getElementById("right_output"),
                right_time_el         = document.getElementById("right_time"),
                //select_mode_el        = document.getElementById("select_mode"),
                
                my_lzma = new LZMA("lzma_worker.js");
            
            if (!String.prototype.trim) {
                String.prototype.trim = function () {
                    return this.replace(/^\s+|\s+$/, "");
                }
            }
            
            function is_array(input) {
                return typeof(input) === "object" && (input instanceof Array);
            }
            
            function convert_formated_hex_to_bytes(hex_str) {
                var count = 0,
                    hex_arr,
                    hex_data = [],
                    hex_len,
                    i;
                
                if (hex_str.trim() == "") return [];
                
                /// Check for invalid hex characters.
                if (/[^0-9a-fA-F\s]/.test(hex_str)) {
                    return false;
                }
                
                hex_arr = hex_str.split(/([0-9a-fA-F]+)/g);
                hex_len = hex_arr.length;
                
                for (i = 0; i < hex_len; ++i) {
                    if (hex_arr[i].trim() == "") {
                        continue;
                    }
                    hex_data[count++] = parseInt(hex_arr[i], 16);
                }
                
                return hex_data;
            }
            
            function convert_to_formated_hex(byte_arr) {
                var hex_str = "",
                    i,
                    len,
                    tmp_hex;
                
                if (!is_array(byte_arr)) {
                    return false;
                }
                
                len = byte_arr.length;
                
                for (i = 0; i < len; ++i) {
                    if (byte_arr[i] < 0) {
                        byte_arr[i] = byte_arr[i] + 256;
                    }
                    tmp_hex = byte_arr[i].toString(16);
                    
                    // Add leading zero.
                    if (tmp_hex.length == 1) tmp_hex = "0" + tmp_hex;
                    
                    if ((i + 1) % 16 === 0) {
                        tmp_hex += "\n";
                    } else {
                        tmp_hex += " ";
                    }
                    
                    hex_str += tmp_hex;
                }
                
                return hex_str.trim();
            }
            
            function update_sizes(compare) {
                var compare_result = "",
                    left_size  = left_text_el.value.length,
                    right_size = convert_formated_hex_to_bytes(right_text_el.value);
                
                if (right_size === false) {
                    right_size = "invalid hex input";
                } else {
                    right_size = right_size.length;
                }
                
                if (compare && right_size > 0 && left_size > 0) {
                    compare_result = " (" + Math.round((right_size / left_size) * 100) + "%)";
                }
                
                left_output_el.innerHTML  = left_size  + " byte" + (left_size  !== 1 ? "s" : "");
                right_output_el.innerHTML = right_size + " byte" + (right_size !== 1 ? "s" : "") + compare_result;
            }
            
            function clear_left() {
                left_text_el.value = "";
                update_sizes();
            }
            
            function clear_right() {
                right_text_el.value = "";
                update_sizes();
            }
            
            function format_time(time) {
                if (time > 1000) {
                    return (time / 1000) + " sec";
                }
                return time + " ms";
            }
            
            
            clear_left_button_el.onclick  = clear_left;
            clear_right_button_el.onclick = clear_right;
            
            

			parse_url_param = function( str )
			{
				var params = str.split("&"), arr = {};
				for (var i=0; i<params.length; i++)
				{
					var temp = params[i].split("=");
					arr[temp[0]] = decodeURIComponent(temp[1]);
				}
				return JSON.stringify(arr);
			}
            
            compress_button_el.onclick = function () {
                var start_time;
                
                if(left_text_el.value.match(/\&/g)  )
                {
					left_text_el.value = parse_url_param(left_text_el.value);
				}
				
                right_text_el.value = "";
                update_sizes();
                
                right_output_el.innerHTML = "Compressing... 0%";
                
                /// Start the clock.
                start_time = (new Date).getTime();
                right_time_el.innerHTML = "";
                
                my_lzma.compress(left_text_el.value, 1, function (result) {
                    right_time_el.innerHTML = format_time((new Date).getTime() - start_time);
                    
                    if (result === false) {
                        alert("An error occurred during compression.");
                        update_sizes();
                        return;
                    }
                    right_text_el.value = convert_to_formated_hex(result).replace(/\s+/g, '');
                    update_sizes(true);
                    
                }, function (percent) {
                    right_output_el.innerHTML = "Compressing... " + Math.round(percent * 100) + "%";
                });
            }
            
            EncodeQueryData = function (data)
			{
			   var ret = [];
			   for (var d in data)
				  ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
			   return ret.join("&");
			}
            
            decompress_button_el.onclick = function () {
               
               
               if(right_text_el.value.substr(2,1) != ' ')
               {
					var a = right_text_el.value.split(''), s = '';
					for (var i=0,j=a.length;i<j;++i)
					{
						if (i>0 && i%2 == 0) s += ' ';
						s += a[i];
					}
					right_text_el.value = s;
				}
                var byte_arr = convert_formated_hex_to_bytes(right_text_el.value),
                    decompressed,
                    start_time;
                
                left_text_el.value = "";
                update_sizes();
                left_output_el.innerHTML = "Decompressing... 0%";
                
                if (byte_arr == false) {
                    ///TODO: Show which character is wrong.  I.e., invalid compressed input: invalid hex character `s
                    alert("invalid compressed input");
                    update_sizes();
                    return false;
                }
                
                /// Start the clock.
                start_time = (new Date).getTime();
                left_time_el.innerHTML = "";
                
                my_lzma.decompress(byte_arr, function (result) {
                    left_time_el.innerHTML = format_time((new Date).getTime() - start_time);
                    
                    if (result === false) {
                        alert("An error occurred during decompression.");
                        return;
                    }
                    
                    left_text_el.value = EncodeQueryData(JSON.parse(result));
                    update_sizes(true);
                }, function (percent) {
                    left_output_el.innerHTML  = "Decompressing... " + Math.round(percent * 100) + "%";
                });
            }
        </script>
    </body>
</html>
