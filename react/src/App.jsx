import { useEffect, useState } from 'react'
import './App.css'
import './App.css'

function App() {

const [users, setUsers] = useState([])
const [loading, setLoading] = useState(true)
  useEffect(() => {
    fetch('http://localhost:8080/api/users/2')
    .then(res => res.json())
    .then(data => {
      console.log(Object.values(data));
      setUsers([Object.values(data)])
    setLoading(false)
    })
    .catch(err => {
    console.error('Błąd pobierania danych:', err)
    setLoading(false)
    })
    }, [])
    return (
      <div className="wrapper">
      <header>
      <h1>
      Lista użytkowników</h1>
      <p>Dane pobrane z backendu</p>
      </header>
      {loading ? (
      <p className="loading">Ładowanie użytkowników...</p>
      ) : (
      <div className="user-grid">
      {users.map(user => (
      <div className="user-card" key={user.id}>
      <h2>{user.name}</h2>
      <p><strong>Nazwa:</strong> {user[0]}</p>
      <p><strong>Email:</strong> {user[1]}</p>
      <p><strong>Miejsce zamieszkania:</strong> {(user[2] === null) ? "NULL" : user[2]}</p>
      </div>
      ))}
    </div>
    )}
  </div>
)


  // const [count, setCount] = useState(0)

  // return (
  //   <>
  //     <div>
  //       <a href="https://vite.dev" target="_blank">
  //         <img src={viteLogo} className="logo" alt="Vite logo" />
  //       </a>
  //       <a href="https://react.dev" target="_blank">
  //         <img src={reactLogo} className="logo react" alt="React logo" />
  //       </a>
  //     </div>
  //     <h1>Vite + React</h1>
  //     <div className="card">
  //       <button onClick={() => setCount((count) => count + 1)}>
  //         count is {count}
  //       </button>
  //       <p>
  //         Edit <code>src/App.jsx</code> and save to test HMR
  //       </p>
  //     </div>
  //     <p className="read-the-docs">
  //       Click on the Vite and React logos to learn more
  //     </p>
  //   </>
  // )
}

export default App
