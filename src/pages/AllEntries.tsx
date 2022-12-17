// noinspection CheckTagEmptyBody
import {useDispatch, useSelector} from "react-redux";
import {useEffect, useState} from "react";
import {stateActions} from "../store";
import linkService from "../services/LinkService";
import SpinnerTimer from "../components/SpinnerTimer";
import {Card, Col, Container, Form, Pagination, Row} from "react-bootstrap";
import {Link} from "../model/link";
import {Category} from "../model/category";
import {AppState} from "../model/AppState";

const recsPerPage = 5;

const AllEntries = () => {
    const dispatch = useDispatch();
    const links: Link[] = useSelector((st: AppState) => st.links);
    const categories: Category[] = useSelector((st: AppState) => st.categories);
    const [busy, setBusy] = useState({state: false, message: ""});
    const [page, setPage] = useState(1);
    const [pageLen, setPageLen] = useState(1);
    const [currPageLinks, setCurrPageLinks] = useState<Link[]>([]);
    const [currPageStartIndex, setCurrPageStartIndex] = useState(1);
    const [filteredLinks, setFilteredLinks] = useState<Link[]>([]);

    useEffect(() => {
        (async () => {
            setBusy({state: true, message: "Loading links from DB..."});
            const locLinksData: any = await linkService.getLinks();
            dispatch(stateActions.setLinks(locLinksData.data));
            const locCategoriesData: any = await linkService.getCategories();
            dispatch(stateActions.setCategories(locCategoriesData.data));
            const numPagesRounded = Math.round(locLinksData.data.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(locLinksData.data);
            setBusy({state: false, message: ""});
        })();
    }, [dispatch]);

    useEffect(() => {
        if (!filteredLinks || filteredLinks.length === 0) {
            return;
        }
        // purpose here is to set the 5 links for the current page (e.g. page=1 should show links 0-4, page=2 should show 5-9, page=3 show 10-14 etc)
        const linksOnPage: Link[] = [];
        const startLinkIndex = (page - 1) * recsPerPage;
        for (let i = startLinkIndex; i < (startLinkIndex + recsPerPage) && i < filteredLinks.length; i++) {
            linksOnPage.push(filteredLinks[i]);
        }
        // console.log("useEffect[page] - page: ", page);
        // console.log("useEffect[page] - linksOnPage: ", linksOnPage);
        // console.log("useEffect[page] - startLinkIndex: ", startLinkIndex);
        setCurrPageStartIndex(startLinkIndex);
        setCurrPageLinks(linksOnPage);
    }, [page, filteredLinks]);

    const handleFirstPage = () => {
        setPage(1);
    };

    const handleNextPage = () => {
        setPage(prev => prev + 1);
    };

    const handlePrevPage = () => {
        setPage(prev => prev - 1);
    };

    const handleLastPage = () => {
        setPage(pageLen - 1);
    };

    const handleCatChange = (evt: any) => {
        const selectedCat = evt.target.value;
        console.log("handleCatChange - selectedCat=" + selectedCat);
        if (!selectedCat || selectedCat.length === 0) {
            const numPagesRounded = Math.round(links.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(links);
        } else {
            const locFilteredLinks = links.filter(link => link.category === selectedCat);
            const numPagesRounded = Math.round(locFilteredLinks.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(locFilteredLinks);
        }
    };

    if (busy.state) {
        return <SpinnerTimer message={busy.message} />;
    } else {
        if (currPageLinks && currPageLinks.length > 0 && categories && categories.length > 0) {
            return (
                <Container className="mt-3">
                    <Row className="text-center mb-3">
                        <Col>{currPageStartIndex + 1}-{currPageStartIndex + currPageLinks.length} of {filteredLinks.length}</Col>
                        <Col>
                            <Form.Select aria-label="Category Selection" size="sm" onChange={handleCatChange}>
                                <option></option>
                                {categories.map(category => (
                                    <option key={category.categoryCd} value={category.categoryCd}>{category.categoryTx}</option>
                                ))}
                            </Form.Select>
                        </Col>
                    </Row>
                    <Row className="text-center">
                        <Col>
                            <Pagination size="lg" className="justify-content-center">
                                <Pagination.First onClick={handleFirstPage} />
                                <Pagination.Prev onClick={handlePrevPage} />
                                <Pagination.Item>{page}</Pagination.Item>
                                <Pagination.Next onClick={handleNextPage} />
                                <Pagination.Last onClick={handleLastPage} />
                            </Pagination>
                        </Col>
                    </Row>
                    {currPageLinks.map(l => (
                        <Card key={l.date_time_link_saved + l.title} border="light">
                            <Card.Header>{categories.filter(cat => cat.categoryCd === l.category).map(cat => cat.categoryTx)}{l.category === "apologetics" ? " (Sent? " + l.sent + ")" : ""}</Card.Header>
                            <Card.Body>
                                <Card.Title>{l.title}</Card.Title>
                                <Card.Text>
                                    <a href={l.url} target="_blank">{l.url}</a><br/>
                                    Date/Time Accessed: {l.date_time_link_saved}<br/>
                                    Addl Comments: {l.addlcomments}
                                </Card.Text>
                            </Card.Body>
                        </Card>
                    ))}
                    <Row className="text-center">
                        <Col>
                            <Pagination size="lg" className="justify-content-center">
                                <Pagination.First onClick={handleFirstPage} />
                                <Pagination.Prev onClick={handlePrevPage} />
                                <Pagination.Item>{page}</Pagination.Item>
                                <Pagination.Next onClick={handleNextPage} />
                                <Pagination.Last onClick={handleLastPage} />
                            </Pagination>
                        </Col>
                    </Row>
                </Container>
            );
        } else {
            return <h3>No Links loaded...</h3>
        }
    }
};

export default AllEntries;