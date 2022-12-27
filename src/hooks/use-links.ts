import {useDispatch} from "react-redux";
import linkService from "../services/LinkService";
import {stateActions} from "../store";

const recsPerPage = 5;
const useLinks = () => {
    const dispatch = useDispatch();

    const refreshSavedLinks = async () => {
        const locLinksData: any = await linkService.getLinks();
        dispatch(stateActions.setLinks(locLinksData.data));
        const locCategoriesData: any = await linkService.getCategories();
        dispatch(stateActions.setCategories(locCategoriesData.data));
    };

    const calculatePageLength = (links: any[]) => {
        const numPagesRounded = Math.floor(links.length / recsPerPage);
        return numPagesRounded + 1;
    };

    return {
        recsPerPage: recsPerPage,
        refreshSavedLinks: refreshSavedLinks,
        calculatePageLength: calculatePageLength
    };
};

export default useLinks;