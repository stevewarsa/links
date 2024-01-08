import {useDispatch} from "react-redux";
import linkService from "../services/LinkService";
import {stateActions} from "../store";
import {Link} from "../model/link";
import {format} from "date-fns-tz";
import {addHours} from "date-fns";

const recsPerPage = 5;
const useLinks = () => {
    const dispatch = useDispatch();

    const refreshSavedLinks = async () => {
        const locLinksData: any = await linkService.getLinks();
        const links: Link[] = locLinksData.data;
        for (const link of links) {
            let savedDt = link.date_time_link_saved;
            const date = new Date(savedDt);
            // apply the date offset from UTC for Arizona
            const updatedDate = addHours(date, -7);
            //2024-01-08 13:40:03 => 2024-01-08 06:40:03
            const pattern = "yyyy-MM-dd HH:mm:ss";
            link.date_time_link_saved = format(updatedDate, pattern);
        }
        dispatch(stateActions.setLinks(locLinksData.data));
        const locCategoriesData: any = await linkService.getCategories();
        dispatch(stateActions.setCategories(locCategoriesData.data));
    };

    const calculatePageLength = (links: any[]) => {
        return links.length < recsPerPage ? 1 : Math.ceil(links.length / recsPerPage);
    };

    return {
        recsPerPage: recsPerPage,
        refreshSavedLinks: refreshSavedLinks,
        calculatePageLength: calculatePageLength
    };
};

export default useLinks;