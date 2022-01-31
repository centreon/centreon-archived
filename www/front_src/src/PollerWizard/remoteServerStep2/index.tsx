import * as React from 'react';

import { connect } from 'react-redux';
import { useNavigate } from 'react-router';
import { isEmpty } from 'ramda';

import { postData, useRequest } from '@centreon/ui';

import Form from '../forms/remoteServer/RemoteServerFormStepTwo';
import routeMap from '../../reactRoutes/routeMap';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import { WizardFormProps } from '../models';

interface Props
  extends Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'> {
  pollerData: Record<string, unknown>;
  setWizard: (pollerWizard) => Record<string, unknown>;
}

const getRemoteServersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

const FormRemoteServerStepTwo = ({
  pollerData,
  setWizard,
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const [remoteServers, setRemoteServers] = React.useState<Record<
    string,
    unknown
  > | null>(null);

  const { sendRequest: getRemoteServersRequest } = useRequest<Array<unknown>>({
    request: postData,
  });
  const { sendRequest: postWizardFormRequest } = useRequest<{
    success: boolean;
    task_id: number | string | null;
  }>({
    request: postData,
  });

  const navigate = useNavigate();

  const filterOutDefaultPoller = (itemArr): Record<string, unknown> => {
    for (let i = 0; i < itemArr.items.length; i += 1) {
      if (itemArr.items[i].id === '1') itemArr.items.splice(i, 1);
    }

    return itemArr;
  };

  const getRemoteServers = (): void => {
    getRemoteServersRequest({
      data: null,
      endpoint: getRemoteServersEndpoint,
    }).then((retrievedRemoteServers) => {
      setRemoteServers(
        isEmpty(retrievedRemoteServers)
          ? { items: [] }
          : filterOutDefaultPoller(retrievedRemoteServers),
      );
    });
  };

  React.useEffect(() => {
    getRemoteServers();
  }, []);

  const handleSubmit = (data): void => {
    const dataToPost = { ...data, ...pollerData };
    dataToPost.server_type = 'remote';

    postWizardFormRequest({
      data: dataToPost,
      endpoint: wizardFormEndpoint,
    })
      .then(({ success, task_id }) => {
        if (success && task_id) {
          setWizard({
            submitStatus: success,
            taskId: task_id,
          });
          goToNextStep();
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  return (
    <Form
      goToPreviousStep={goToPreviousStep}
      pollers={remoteServers}
      onSubmit={handleSubmit}
    />
  );
};

const mapStateToProps = ({ pollerForm }): Pick<Props, 'pollerData'> => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const RemoteServerStepTwo = connect(
  mapStateToProps,
  mapDispatchToProps,
)(FormRemoteServerStepTwo);

export default (
  props: Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'>,
): JSX.Element => {
  return <RemoteServerStepTwo {...props} />;
};
