import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        token: localStorage.getItem('makeup-token')
    },
    mutations: {
        setToken(state, token) {
            localStorage.setItem('makeup-token', token);
            state.token = token;
        },
        deleteToken(state) {
            localStorage.removeItem('makeup-token');
        }
    },
    getters: {
        token(state) {
            return state.token;
        },
        loggedIn(state) {
            if (state.token !== null) {
                return true;
            } else {
                return false;
            }
        },
        user(state) {
            return new Promise((resolve, reject) => {
                fetch('http://localhost:3001/users/me', {
                    headers: {
                        Authorization: state.token
                    }
                }).then(res => res.json()).then(user => {
                    console.log('asd');
                    resolve(user);
                });
            });
        }
    },
    actions: {}
});