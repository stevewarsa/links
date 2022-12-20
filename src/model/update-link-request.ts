import {Link} from "./link";

export class UpdateLinkRequest {
    link: Link;
    hasNewCat: boolean;
    newCatCd: string;
    newCatTx: string;
}