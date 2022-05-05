import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import { federatedComponentsAtom } from '../../federatedComponents/atoms';
import { Remote } from '../../federatedComponents/load';
import { FederatedComponent } from '../../federatedComponents/models';

interface Props {
  federatedComponents: Array<FederatedComponent>;
}

const LoadableComponents = ({
  federatedComponents,
  ...rest
}: Props): JSX.Element | null => {
  return useMemoComponent({
    Component: (
      <>
        {federatedComponents.map(({ remoteEntry, name, hooks, moduleName }) => {
          return hooks.map((component) => {
            return (
              <Remote
                isHook
                component={component}
                key={name}
                moduleName={moduleName}
                name={name}
                remoteEntry={remoteEntry}
                {...rest}
              />
            );
          });
        })}
      </>
    ),
    memoProps: [federatedComponents],
  });
};

const Hook = (props): JSX.Element | null => {
  const federatedComponents = useAtomValue(federatedComponentsAtom);

  if (isNil(federatedComponents)) {
    return null;
  }

  console.log(federatedComponents);

  return (
    <LoadableComponents federatedComponents={federatedComponents} {...props} />
  );
};

export default Hook;
