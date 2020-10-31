import React, { useState } from 'react';
import axios from 'axios';

const UploadForm = () => {
  const [name, setName] = useState("");
  const [selectedFile, setSelectedFile] = useState(null);

  const submitForm = async () => {
    const formData = new FormData();
    formData.append("name", 'callsfile');
    formData.append("file", selectedFile);

    try {
      // const response = await axios.post('http://localhost:8000/calls', formData);
      const response = await axios({
        method: 'post',
        url: 'http://myapp:8000/calls',
        data: formData,
        headers: {
          'content-type': `multipart/form-data; boundary=${formData._boundary}`,
        },
      });
      alert("File Upload success" + response.data);
    } catch(err) {
      alert("File Upload Error: " + err.request);
    }
  };

  return (
    <div className="upload__form">
      <form>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
        />

        <input
          type="file"
          onChange={(e) => setSelectedFile(e.target.files[0])}
        />
      </form>
      <button onClick={submitForm}>Submit</button>
    </div>
  );
};

export {UploadForm};
