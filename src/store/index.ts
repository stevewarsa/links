import {configureStore, createSlice} from "@reduxjs/toolkit";
import {AppState} from "../model/AppState";
import {Link} from "../model/link";
const initialState: AppState = {
  links: [],
  categories: []
};
const state = createSlice({
  name: "state",
  initialState: initialState,
  reducers: {
    setLinks(state, action) {
      let links: Link[] = action.payload;
      // de-dupe the list...
      links = links.filter((link, index, self) => self.findIndex(t => t.url === link.url && t.date_time_link_saved === link.date_time_link_saved && t.title === link.title && t.category === link.category && t.sent === link.sent) === index)
      state.links = links;
    },
    setCategories(state, action) {
      state.categories = action.payload;
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
