// noinspection CheckTagEmptyBody

import {useDispatch} from "react-redux";
import 'ag-grid-community/dist/styles/ag-grid.css';
import 'ag-grid-community/dist/styles/ag-theme-alpine.css';
import {useEffect, useState} from "react";
import {stateActions} from "../store";
import linkService from "../services/LinkService";
import SpinnerTimer from "../components/SpinnerTimer";

const AllEntries = () => {
    const dispatch = useDispatch();
    const [busy, setBusy] = useState({state: false, message: ""});

    useEffect(() => {
        (async () => {
            setBusy({state: true, message: "Loading weight entries from DB..."});
            const locLinksData: any = await linkService.getLinks();
            dispatch(stateActions.setLinks(locLinksData.data));
            const locCategoriesData: any = await linkService.getCategories();
            dispatch(stateActions.setCategories(locCategoriesData.data));
            setBusy({state: false, message: ""});
        })();
    }, [dispatch]);

    if (busy.state) {
        return <SpinnerTimer key="loading-links" message={busy.message} />;
    } else {
        return <h1>Links</h1>;
    }
};

export default AllEntries;