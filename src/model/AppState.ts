import {Link} from "./link";
import {Category} from "./category";

export interface AppState {
    links: Link[];
    categories: Category[];
}