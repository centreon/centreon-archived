import * as React from 'react';

import { isNil, propOr } from 'ramda';
import { useHref } from 'react-router';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import { dynamicImport } from '../../helpers/dynamicImport';
import MenuLoader from '../MenuLoader';
import { externalComponentsAtom } from '../../externalComponents/atoms';
import ExternalComponents, {
  ExternalComponent,
} from '../../externalComponents/models';

interface Props {
  hooks?: ExternalComponent;
  path: string;
}

const LoadableHooks = ({ hooks, path, ...rest }: Props): JSX.Element | null => {
  const basename = useHref('/');

  if (isNil(hooks)) {
    return null;
  }

  return (
    <>
      {Object.entries(hooks)
        .filter(([hook]) => hook.includes(path))
        .map(([, parameters]) => {
          const HookComponent = React.lazy(() =>
            dynamicImport(basename, parameters),
          );

          return (
            <React.Suspense fallback={<MenuLoader width={29} />} key={path}>
              <HookComponent {...rest} />
            </React.Suspense>
          );
        })}
    </>
  );
};

const Hook = (props: Pick<Props, 'path'>): JSX.Element | null => {
  const externalComponents = useAtomValue(externalComponentsAtom);

  const hooks = propOr<undefined, ExternalComponents | null, ExternalComponent>(
    undefined,
    'hooks',
    externalComponents,
  );

  return useMemoComponent({
    Component: <LoadableHooks {...props} hooks={hooks} />,
    memoProps: [hooks],
  });
};

export default Hook;
