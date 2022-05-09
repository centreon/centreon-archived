import { lazy, Suspense } from 'react';

import { isNil, propOr } from 'ramda';
import { useHref } from 'react-router-dom';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent, MenuSkeleton } from '@centreon/ui';

import { dynamicImport } from '../../helpers/dynamicImport';
import { externalComponentsAtom } from '../../externalComponents/atoms';
import ExternalComponents, {
  ExternalComponent,
} from '../../externalComponents/models';

interface Props {
  hooks: ExternalComponent;
  path: string;
}

const LoadableHooks = ({ hooks, path, ...rest }: Props): JSX.Element | null => {
  const basename = useHref('/');

  return useMemoComponent({
    Component: (
      <>
        {Object.entries(hooks)
          .filter(([hook]) => hook.includes(path))
          .map(([, parameters]) => {
            const HookComponent = lazy(() =>
              dynamicImport(basename, parameters),
            );

            return (
              <Suspense fallback={<MenuSkeleton width={29} />} key={path}>
                <HookComponent {...rest} />
              </Suspense>
            );
          })}
      </>
    ),
    memoProps: [hooks],
  });
};

const Hook = (props: Pick<Props, 'path'>): JSX.Element | null => {
  const externalComponents = useAtomValue(externalComponentsAtom);

  const hooks = propOr<undefined, ExternalComponents | null, ExternalComponent>(
    undefined,
    'hooks',
    externalComponents,
  );

  if (isNil(hooks)) {
    return null;
  }

  return <LoadableHooks hooks={hooks} {...props} />;
};

export default Hook;
