## Adding Backend-Styles

**Super-Root only!**

You can create new Styles for the Backend to fit the Backend to your / your Company's Branding

to add a new Style:

1. go to http://jqueryui.com/themeroller and select/create a new Style
2. download your Style with all components (full package)
3. create a new unique Style-Folder under backend/inc/css/ (Folder has to be writable!)
4. copy from the package/css/ images and the main stylesheet AND rename it to "jquery-ui.css" (no Version-Numbers!)
5. copy the compressed Style-Hash from the Themeroller-URL
6. decompress it via the Wizard extension/documentation/wizards/de_compress
7. save the decompressed hash into a File called "parameter.txt" in your Style-Folder (it is used to adapt some Values in the Base-CSS)
8. Save / create a Thumbnail for the Style-Selector called "preview.png" and copy it to your Style-Folder
9. run Admin_Wizards > Script-Manager > CSS-Packer

Alternatives to ThemeRoller may be...

* http://jqueryuithemegallery.just-page.de
* http://jquit.com/builder
* http://www.warfuric.com/taitems/demo.html

atm you need to declare these Variables 
 ffDefault=&cornerRadius=&bgColorContent=&borderColorContent=&fcContent=&bgColorDefault=&bgImgOpacityDefault=&borderColorDefault=&fcDefault=&bgColorActive=&fcActive=&bgColorHighlight=&borderColorHighlight=&fcHighlight=&bgColorError=

