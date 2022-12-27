// noinspection CheckTagEmptyBody
import {useDispatch, useSelector} from "react-redux";
import {useEffect, useState} from "react";
import {stateActions} from "../store";
import linkService from "../services/LinkService";
import SpinnerTimer from "../components/SpinnerTimer";
import {Alert, Button, Card, Col, Container, Form, Modal, Pagination, Row} from "react-bootstrap";
import {Link} from "../model/link";
import {Category} from "../model/category";
import {AppState} from "../model/AppState";
import {UpdateLinkRequest} from "../model/update-link-request";
import useLinks from "../hooks/use-links";


const AllEntries = () => {
    const dispatch = useDispatch();
    const links: Link[] = useSelector((st: AppState) => st.links);
    const categories: Category[] = useSelector((st: AppState) => st.categories);
    const [busy, setBusy] = useState({state: false, message: ""});
    const {refreshSavedLinks, calculatePageLength, recsPerPage} = useLinks();
    const [page, setPage] = useState(1);
    const [pageLen, setPageLen] = useState(1);
    const [currPageLinks, setCurrPageLinks] = useState<Link[]>([]);
    const [currPageStartIndex, setCurrPageStartIndex] = useState(1);
    const [filteredLinks, setFilteredLinks] = useState<Link[]>([]);
    const [selectedCat, setSelectedCat] = useState<string>("");
    const [sentVal, setSentVal] = useState("All");
    const [searchText, setSearchText] = useState("");
    const [showEditLink, setShowEditLink] = useState(false);
    const [linkToEdit, setLinkToEdit] = useState<Link>(null);
    const [linkNewCat, setLinkNewCat] = useState<Category>(null);
    const [showAlert, setShowAlert] = useState({visible: false, success: true, headerText: "", bodyText: ""});

    useEffect(() => {
        // when this component initially loads, get the links if not already loaded
        if (!links || links.length === 0) {
            console.log("AllEntries.useEffect[dispatch] - Links are not loaded yet, so call custom hook to load them...");
            handleRefreshSavedLinks();
        }
    }, []);

    useEffect(() => {
        if (!links) {
            return;
        }
        // When the links from the store get updated, this function will fire
        setPage(1);
        setFilteredLinks(links);
    }, [links]);

    useEffect(() => {
        if (!filteredLinks) {
            return;
        }
        // purpose here is to set the 5 links for the current page (e.g. page=1 should show links 0-4, page=2 should show 5-9, page=3 show 10-14 etc)
        const linksOnPage: Link[] = [];
        const startLinkIndex = (page - 1) * recsPerPage;
        for (let i = startLinkIndex; i < (startLinkIndex + recsPerPage) && i < filteredLinks.length; i++) {
            linksOnPage.push(filteredLinks[i]);
        }
        setCurrPageStartIndex(startLinkIndex);
        setCurrPageLinks(linksOnPage);
        document.documentElement.scrollTop = 0;
    }, [page, filteredLinks]);

    const handleRefreshSavedLinks = () => {
        setBusy({state: true, message: "Loading links from DB..."});
        // the result of this call will be that the store will be updated with all links and categories
        // note - this is a call to a custom hook
        console.log("AllEntries.handleRefreshSavedLinks - request to load links, so call custom hook to load them...");
        refreshSavedLinks().then(() => {
            setBusy({state: false, message: ""});
        });
    };

    const handleFirstPage = () => {
        setPage(1);
    };

    const handleNextPage = () => {
        console.log("handleNextPage - page=" + page + ", pageLen=" + pageLen);
        if (page === pageLen) {
            return;
        }
        setPage(prev => prev + 1);
    };

    const handlePrevPage = () => {
        if (page === 1) {
            return;
        }
        setPage(prev => prev - 1);
    };

    const handleLastPage = () => {
        setPage(pageLen);
    };

    const doFilter = (cat: string, sent: string, search: string) => {
        let locFilteredLinks = links.filter(link => cat.length === 0 || link.category === cat);
        if (cat === "apologetics") {
            console.log("doFilter - category is apologetics, filtering to sent value " + sent);
            locFilteredLinks = locFilteredLinks.filter(link => sent === "All" || link.sent === sent);
        }
        if (search !== "") {
            console.log("doFilter - search string is " + search);
            locFilteredLinks = locFilteredLinks.filter(link => {
                return link.title && link.title.toUpperCase().includes(search.toUpperCase());
            });
        }
        setPageLen(calculatePageLength(locFilteredLinks));
        setPage(1);
        setFilteredLinks(locFilteredLinks);
    }

    const handleCatChange = (evt: any) => {
        const locSelectedCat: string = evt.target.value;
        console.log("handleCatChange - selectedCat=" + locSelectedCat);
        if (!locSelectedCat || locSelectedCat.length === 0) {
            const numPagesRounded = Math.round(links.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(links);
            setSelectedCat("");
        } else {
            setSelectedCat(locSelectedCat);
            doFilter(locSelectedCat, sentVal, searchText);
        }
    };

    const handleSent = (evt: any, cbkVal: string) => {
        console.log("handleSent - here's the cbkVal: " + cbkVal + ", here's the current value of sent: " + sentVal);
        console.log("handleSent - here's the event: ", evt);
        const checked: boolean = evt.target.checked;
        let newSentVal: string = sentVal;
        if (checked) {
            // if only one of the checkboxes was checked and the incoming one is now checked,
            // then all recs should be displayed
            if (sentVal === "N" || sentVal === "Y") {
                newSentVal = "All";
            } else if (sentVal === "All") {
                // 'All' means neither of the checkboxes were checked, so now we're
                // filtering down to the newly checked box
                newSentVal = cbkVal;
            }
        } else {
            // if only one of the checkboxes was checked and the incoming one is now checked,
            // then all recs should be displayed
            if (sentVal === "N" || sentVal === "Y") {
                newSentVal = "All";
            }
        }
        setSentVal(newSentVal);
        console.log("handleSent - calling doFilter(" + selectedCat + ", " + newSentVal + ")");
        doFilter(selectedCat, newSentVal, searchText);
    };

    const handleSearchChange = (evt: any) => {
        const locSearchText = evt.target.value.trim();
        setSearchText(locSearchText);
        doFilter(selectedCat, sentVal, locSearchText);
    };

    const handleRandomLink = () => {
        let randomIndex: number = Math.floor(Math.random() * (filteredLinks.length));
        window.open(filteredLinks[randomIndex].url, "_blank");
    };

    const handleCancel = () => {
        setShowEditLink(false);
    };

    const handleSubmitUpdateLink = async () => {
        setShowEditLink(false);
        setBusy({state: true, message: "Updating link..."});
        const updateLinkRequest: UpdateLinkRequest = new UpdateLinkRequest();
        updateLinkRequest.link = linkToEdit;
        updateLinkRequest.hasNewCat = linkNewCat != null;
        updateLinkRequest.newCatCd = linkNewCat?.categoryCd;
        updateLinkRequest.newCatTx = linkNewCat?.categoryTx;
        linkService.updateLink(updateLinkRequest).then(resp => {
            if (resp.data === "TRUE") {
                setShowAlert(prev => {
                    return {...prev, success: true, visible: true, headerText: "Updated link!", bodyText: "Link " + linkToEdit.id + " successfully!"}
                });
                const newLinks = links.map(element => element.id === linkToEdit.id ? linkToEdit : element);
                dispatch(stateActions.setLinks(newLinks));
                setFilteredLinks(prev => prev.map(element => element.id === linkToEdit.id ? linkToEdit : element));
                if (updateLinkRequest.hasNewCat) {
                    dispatch(stateActions.setCategories([...categories, linkNewCat]));
                }
            } else {
                setShowAlert(prev => {
                    return {...prev, success: false, visible: true, headerText: "Link Not Updated!", bodyText: "Unable to update link " + linkToEdit.id + " - check logs"}
                });
            }
            setBusy({state: false, message: ""});
        });
    };

    const handleEditLink = (link: Link) => {
        setLinkToEdit({...link});
        setShowEditLink(true);
    };

    const handleEditLinkCatChange = (evt: any) => {
        setLinkToEdit(prev => {
            return {...prev, category: evt.target.value};
        });
    };

    const handleUrlChange = (evt: any) => {
        setLinkToEdit(prev => {
            return {...prev, url: evt.target.value.trim()};
        });
    };

    const handleTitleChange = (evt: any) => {
        setLinkToEdit(prev => {
            return {...prev, title: evt.target.value.trim()};
        });
    };

    const handleAddlCommentsChange = (evt: any) => {
        setLinkToEdit(prev => {
            return {...prev, addlcomments: evt.target.value.trim()};
        });
    };

    const handleNewCatCdChange = (evt: any) => {
        setLinkToEdit(prev => {
            return {...prev, category: evt.target.value.trim()};
        });
        setLinkNewCat(prev => {
            if (prev === null) {
                return {categoryCd: evt.target.value.trim(), categoryTx: evt.target.value.trim()};
            } else {
                return {...prev, categoryCd: evt.target.value.trim()};
            }
        });
    };

    const handleNewCatTxChange = (evt: any) => {
        setLinkNewCat(prev => {
            // assume the user will edit the code first and by the time we get here prev will not be null
            return {...prev, categoryTx: evt.target.value.trim()};
        });
    };

    const handleRefresh = () => {
        handleRefreshSavedLinks();
    };

    if (busy.state) {
        return <SpinnerTimer message={busy.message} />;
    } else {
        if (currPageLinks && categories && categories.length > 0) {
            return (
                <Container className="mt-3">
                    {showAlert.visible &&
                        <Alert variant={showAlert.success ? "success" : "danger"} onClose={() => setShowAlert(prev => {
                            return {...prev, visible: false}
                        })} dismissible>
                            <Alert.Heading>{showAlert.headerText}</Alert.Heading>
                            <p>{showAlert.bodyText}</p>
                        </Alert>
                    }
                    {currPageLinks.length > 0 &&
                        <Row className="text-center mb-3">
                            <div className="col-10">{currPageStartIndex + 1}-{currPageStartIndex + currPageLinks.length} of {filteredLinks.length}</div>
                            <div className="col-2"><Button onClick={handleRefresh} variant="outline-primary" size="sm"><i className="fa fa-refresh"></i></Button> </div>
                        </Row>
                    }
                    <Row className="text-center mb-3">
                        <Col>
                            <Form.Select aria-label="Category Selection" size="sm" onChange={handleCatChange}>
                                <option value=""></option>
                                {categories.map(category => (
                                    <option key={category.categoryCd} value={category.categoryCd}>{category.categoryTx}</option>
                                ))}
                            </Form.Select>
                        </Col>
                        {selectedCat && "apologetics" === selectedCat &&
                        <Col>
                            <Form.Check checked={sentVal === "Y"} onChange={(evt) => handleSent(evt, "Y")} inline label="Y" name="sent_y" type="checkbox" id="Y" />
                            <Form.Check checked={sentVal === "N"} onChange={(evt) => handleSent(evt, "N")} inline label="N" name="sent_n" type="checkbox" id="N" />
                            <span style={{fontSize: "10px"}}>(sent)</span>
                        </Col>
                        }
                    </Row>
                    {selectedCat && "apologetics" === selectedCat &&
                        <Row className="mb-3">
                            <Col>
                                <Button onClick={handleRandomLink} size="lg" variant="primary">Random Link</Button>
                            </Col>
                        </Row>
                    }
                    {currPageLinks.length > 0 &&
                        <Row className="text-center mb-3">
                            <Col>
                                <Form.Control
                                    type="text"
                                    id="searchText"
                                    placeholder="Search Text"
                                    onChange={handleSearchChange}
                                />
                            </Col>
                        </Row>
                    }
                    {currPageLinks.length > 0 &&
                        <Row className="text-center">
                            <Col>
                                <Pagination size="lg" className="justify-content-center">
                                    <Pagination.First onClick={handleFirstPage} />
                                    <Pagination.Prev disabled={page === 1} onClick={handlePrevPage} />
                                    <Pagination.Item disabled>{page}</Pagination.Item>
                                    <Pagination.Next disabled={page === pageLen} onClick={handleNextPage} />
                                    <Pagination.Last onClick={handleLastPage} />
                                </Pagination>
                            </Col>
                        </Row>
                    }
                    {currPageLinks.length === 0 && selectedCat === "apologetics" && sentVal === "N" && <h3>No links unsent for category Apologetics</h3>}
                    {currPageLinks.length === 0 && selectedCat !== "apologetics" && <h3>No links for category '{selectedCat}'</h3>}
                    {currPageLinks.length > 0 && currPageLinks.map(l => (
                        <Card key={l.date_time_link_saved + l.title} border="light">
                            <Card.Header><strong>{categories.filter(cat => cat.categoryCd === l.category).map(cat => cat.categoryTx)}</strong>{l.category === "apologetics" ? " (Sent? " + l.sent + ")" : ""}</Card.Header>
                            <Card.Body>
                                <Card.Title>{l.title}</Card.Title>
                                <Card.Text>
                                    <strong>URL:</strong> <a href={l.url} target="_blank" rel="noreferrer">{l.url}</a><br/>
                                    <strong>Date/Time Accessed:</strong> {l.date_time_link_saved}
                                    {l.addlcomments !== "" && <br/>}
                                    {l.addlcomments !== "" && "Addl Comments: " + l.addlcomments}
                                </Card.Text>
                                <Button onClick={() => handleEditLink(l)} variant="outline-primary"><i className="fa fa-edit"></i> Edit</Button>
                            </Card.Body>
                        </Card>
                    ))}
                    {currPageLinks.length > 0 &&
                        <Row className="text-center">
                            <Col>
                                <Pagination size="lg" className="justify-content-center">
                                    <Pagination.First onClick={handleFirstPage} />
                                    <Pagination.Prev disabled={page === 1} onClick={handlePrevPage} />
                                    <Pagination.Item disabled>{page}</Pagination.Item>
                                    <Pagination.Next disabled={page === pageLen} onClick={handleNextPage} />
                                    <Pagination.Last onClick={handleLastPage} />
                                </Pagination>
                            </Col>
                        </Row>
                    }
                    {linkToEdit != null &&
                        <Modal show={showEditLink} onHide={handleCancel}>
                            <Modal.Header closeButton>
                                <Modal.Title>Edit Link</Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <Row>
                                    <Col>URL:</Col>
                                </Row>
                                <Row>
                                    <Col><textarea className="w-100" value={linkToEdit?.url} rows={3} id="url"
                                                   placeholder="URL"
                                                   onChange={handleUrlChange}></textarea></Col>
                                </Row>
                                <Row>
                                    <Col>Title:</Col>
                                </Row>
                                <Row>
                                    <Col><textarea className="w-100" value={linkToEdit?.title} rows={3} id="title"
                                                   placeholder="Title"
                                                   onChange={handleTitleChange}></textarea></Col>
                                </Row>
                                <Row>
                                    <Col>Add'l Comments:</Col>
                                </Row>
                                <Row>
                                    <Col><textarea className="w-100" value={linkToEdit?.addlcomments} rows={3} id="addlcomments"
                                                   placeholder="Additional Comments"
                                                   onChange={handleAddlCommentsChange}></textarea></Col>
                                </Row>
                                <Row>
                                    <Col>Category:</Col>
                                </Row>
                                <Row>
                                    <Col>
                                        <Form.Select value={linkToEdit.category} aria-label="Edit Link Category Selection" size="sm"
                                                     onChange={handleEditLinkCatChange}>
                                            {categories?.map(category => (
                                                <option key={category.categoryCd}
                                                        value={category.categoryCd}>{category.categoryTx}</option>
                                            ))}
                                        </Form.Select>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col>New Category:</Col>
                                </Row>
                                <Row>
                                    <Col><Form.Control type="text" id="newcatcd"
                                                              placeholder="New Category Code"
                                                              onChange={handleNewCatCdChange}/></Col>
                                </Row>
                                <Row>
                                    <Col><Form.Control type="text" id="newcattx"
                                                              placeholder="New Category Text"
                                                              onChange={handleNewCatTxChange}/></Col>
                                </Row>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button variant="primary" onClick={handleSubmitUpdateLink}>Submit</Button>
                                <Button variant="secondary" onClick={handleCancel}>Cancel</Button>
                            </Modal.Footer>
                        </Modal>
                    }
                </Container>
            );
        } else {
            return <h3>No Links loaded...</h3>
        }
    }
};

export default AllEntries;