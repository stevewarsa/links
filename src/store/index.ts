import {configureStore, createSlice} from "@reduxjs/toolkit";
import {AppState} from "../model/AppState";
const initialState: AppState = {
  links: []
};
const state = createSlice({
  name: "state",
  initialState: initialState,
  reducers: {
    setLinks(state, action) {
      state.links = action.payload;
    }
  }
});

const store = configureStore({
  reducer: state.reducer,
  middleware: (getDefaultMiddleware) => getDefaultMiddleware({
    serializableCheck: false,
  })
});
export const stateActions = state.actions;
export default store;
