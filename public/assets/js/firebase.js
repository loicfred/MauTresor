import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyDz3H38XDOCTMPo7ujErq8laeOVJDY-qdg",
    authDomain: "mautresor-defcc.firebaseapp.com",
    projectId: "mautresor-defcc",
    appId: "1:480424944215:web:657b70fb2cfff78e1ff912"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

window.auth = auth;
