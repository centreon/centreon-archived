import { combineReducers , createStore, applyMiddleware } from 'redux'
import thunk from 'redux-thunk'
import { createLogger } from 'redux-logger'
import UserReducer from '../Header/Reducers/UserReducer'

const logger = createLogger()

const rootReducer = combineReducers({
  user: UserReducer
})

export const store = createStore(
  rootReducer,
  applyMiddleware(thunk, logger)
)