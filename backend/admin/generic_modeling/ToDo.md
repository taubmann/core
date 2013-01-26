# ToDo

## Objective

make the Management of Micro-Models as smooth as possible

1. Editor to create/manage/delete Micro-Models like Modeling
   1. Micro-Models should reside in a Sub-Folder of "objects/"
   2. they should be saved as PHP-Files (to prevent direct access in any Case)
   3. if a Model is changed, the Structure in every Entry using this Model should be adapted (while leaving the Values inside the Structure untouched)
   4. optional: if a Model is deleted, the Entries, using this Model should be cleared as well
   5. optional: to get real SQL-Filtering on this Model, a generic Helper-Table can be used containing *parentID*, *FieldName* and *Value* (Unique-Index spanning over parentID+FieldName)
2. 
