<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js"></script>
<div data-role="header">
    <h1>Upload file</h1>
</div>
<div data-role="content">
<div id="loading" style="display: none;"><progress></progress>

</div>
    <form id="uploadForm" method="post" enctype="multipart/form-data">
    <div id="uploadResult">[]</div>

        <h2>Upload File</h2>
        <div data-role="fieldcontain">
            <label for="fileToUpload">File:</label>
            <input type="file" name="fileToUpload" id="fileToUpload" required/>
        </div>
        
        <div data-role="fieldcontain">
            <label for="categoryId">Category ID:</label>
            <input type="text" name="categoryId" id="categoryId" required/>
        </div>
        
        <div data-role="fieldcontain">
            <label for="description">Description:</label>
            <textarea cols="40" rows="8" name="description" id="description"></textarea>
        </div>

        <div data-role="fieldcontain">
            <label for="qualityMark">Quality Mark:</label>
            <input type="text" name="qualityMark" id="qualityMark" required/>
        </div>
        
        <div data-role="fieldcontain">
            <label for="uniquenessMark">Uniqueness Mark:</label>
            <input type="text" name="uniquenessMark" id="uniquenessMark" required/>
        </div>
        
        <div data-role="fieldcontain">
            <label for="interfaceLanguage">Interface Language:</label>
            <input type="text" name="interfaceLanguage" id="interfaceLanguage" required/>
        </div>
        
        <div data-role="fieldcontain">
            <label for="uploadedBy">Uploaded By:</label>
            <input type="text" name="uploadedBy" id="uploadedBy" required/>
        </div>

        <div data-role="fieldcontain">
            <label for="fileNameHuman">Colloquial File Name:</label>
            <input type="text" name="fileNameHuman" id="fileNameHuman" required/>
        </div>
        
        <button type="button" id="submit" data-role="button" data-inline="true" data-theme="b">Upload</button>
        
    </form>
</div>