import Cookies from 'js-cookie'
import * as types from '../mutation-types'

// state
export const state = {
  jobs: null,
  process: null,
}

// getters
export const getters = {
  jobs: state => state.jobs,
  process: state => state.process
}

// mutations
export const mutations = {
  // [types.FETCH_JOB] (state, { locale }) {
  //   state.locale = locale
  // }
}

// actions
export const actions = {
  // setLocale ({ commit }, { locale }) {
  //   commit(types.SET_LOCALE, { locale })

  //   Cookies.set('locale', locale, { expires: 365 })
  // }
}
