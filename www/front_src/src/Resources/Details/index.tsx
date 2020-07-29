import * as React from 'react';

import { getData, useRequest, SlidePanel } from '@centreon/ui';

import Header from './Header';
import Body from './Body';
import { ResourceDetails } from './models';
import { ResourceEndpoints } from '../models';
import { useResourceContext } from '../Context';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const [details, setDetails] = React.useState<ResourceDetails>();

  const {
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  } = useResourceContext();

  const {
    details: detailsEndpoint,
  } = selectedDetailsEndpoints as ResourceEndpoints;

  const clearSelectedResource = (): void => {
    setSelectedDetailsEndpoints(null);
  };

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    sendRequest(detailsEndpoint).then((retrievedDetails) =>
      setDetails(retrievedDetails),
    );
  }, [detailsEndpoint]);

  return (
    <SlidePanel
      header={<Header details={details} onClickClose={clearSelectedResource} />}
      content={
        <Body
          details={details}
          endpoints={selectedDetailsEndpoints as ResourceEndpoints}
          openTabId={detailsTabIdToOpen}
          onSelectTab={setDefaultDetailsTabIdToOpen}
        />
      }
    />
  );
};

export default Details;
