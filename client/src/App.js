import { useState, useEffect } from 'react';
import './App.css';
import { UploadForm } from './UploadForm';
import { Table } from 'react-bootstrap';

function App() {
  const [callsData, setCallsData] = useState({})

  useEffect(() => {
    const eventSource = new EventSource('http://mercure:3000/.well-known/mercure?topic=' + encodeURIComponent('http://commpeak.com/calls/1'));
    eventSource.onmessage = event => {
      // Will be called every time an update is published by the server
      // alert("Data from mercure");
      setCallsData(JSON.parse(event.data));
    }
  }, [])

  const listItems = Object.keys(callsData).map((call) =>
    <tr>
      <th>{call}</th>
      <th>{callsData[call]['same_calls']}</th>
      <th>{callsData[call]['same_duration']}</th>
      <th>{callsData[call]['total_calls']}</th>
      <th>{callsData[call]['total_duration']}</th>
    </tr>
  );

  return (
    <div className="App">
      <header className="App-header">
        <UploadForm />
        <Table striped bordered hover>
          <thead>
            <tr>
              <th>Customer ID</th>
              <th>Same Calls</th>
              <th>Same Duration</th>
              <th>Total Calls</th>
              <th>Total Duration</th>
            </tr>
          </thead>
          <tbody>
            {listItems}
          </tbody>
        </Table>
      </header>
    </div>
  );
}

export default App;
